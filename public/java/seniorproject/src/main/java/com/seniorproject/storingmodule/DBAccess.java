/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.storingmodule;

import au.com.bytecode.opencsv.CSVWriter;
import com.seniorproject.graphmodule.Edge;
import com.seniorproject.graphmodule.EdgeIterable;
import com.seniorproject.graphmodule.Node;
import com.seniorproject.graphmodule.NodeIterable;
import iot.jcypher.database.DBAccessFactory;
import iot.jcypher.database.DBProperties;
import iot.jcypher.database.DBType;
import iot.jcypher.database.IDBAccess;
import iot.jcypher.graph.GrNode;
import iot.jcypher.graph.GrRelation;
import iot.jcypher.graph.Graph;
import iot.jcypher.query.JcQueryResult;
import iot.jcypher.query.result.JcError;
import iot.jcypher.transaction.ITransaction;
import static iot.jcypher.util.Util.appendErrorList;
import java.io.FileWriter;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Map.Entry;
import java.util.Properties;
import java.util.Set;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.Semaphore;
import org.neo4j.graphdb.Direction;
import org.neo4j.graphdb.DynamicLabel;
import org.neo4j.graphdb.GraphDatabaseService;
import org.neo4j.graphdb.Label;
import org.neo4j.graphdb.Relationship;
import org.neo4j.graphdb.ResourceIterator;
import org.neo4j.graphdb.Result;
import org.neo4j.graphdb.Transaction;
import org.neo4j.graphdb.factory.GraphDatabaseFactory;
import org.neo4j.rest.graphdb.RestGraphDatabase;

public class DBAccess {

    private static ExecutorService pool = Executors.newFixedThreadPool(4);
    private static Semaphore semaphore = new Semaphore(4);

    private static IDBAccess dbAccess;

    public DBAccess() {

    }

    public static String toRegex(List<String> s) {
        String tmp = s.get(0);
        for (int i = 1; i < s.size(); i++) {
            tmp += tmp + "|" + s.get(i);
        }
        return tmp;
    }

    private static void initDBConnection() {
        Properties props = new Properties();
        props.setProperty(DBProperties.SERVER_ROOT_URI, Config.HOST_NAME);
        dbAccess = DBAccessFactory.createDBAccess(DBType.REMOTE, props, Config.USERNAME, Config.PASSWORD);
    }

    public com.seniorproject.graphmodule.Graph loadAll(Map<String, List<String>> sFilters,
            Map<String, List<Double>> fFilters, String db) {

        Set<Node> nodes = new HashSet<>();
        List<Edge> edges = new ArrayList<>();

        String rnCode_Regex = "";
        String callDay_Regex = "";

        for (String rnCode : sFilters.get("rnCode")) {
            rnCode_Regex = rnCode_Regex + rnCode + "|";
        }
        for (String callDay : sFilters.get("callDay")) {
            callDay_Regex = callDay_Regex + callDay + "|";
        }
        rnCode_Regex = rnCode_Regex.substring(0, rnCode_Regex.length()-1);
        callDay_Regex = callDay_Regex.substring(0, callDay_Regex.length()-1);
        GraphDatabaseService graphDb = null;
        
        try {
            graphDb = new GraphDatabaseFactory().newEmbeddedDatabase("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/database/Neo4j/store.graphdb");

            Map<String, Object> params = new HashMap<>();
            params.put("rnCode", rnCode_Regex);
            params.put("callDay", callDay_Regex);
            params.put("durationMin", fFilters.get("duration").get(0));
            params.put("durationMax", fFilters.get("duration").get(1));
            params.put("startTime", fFilters.get("startTime").get(0));
            params.put("endTime", fFilters.get("startTime").get(1));
            params.put("startDate", fFilters.get("startDate").get(0));
            params.put("endDate", fFilters.get("startDate").get(1));
            params.put("incomingMin", 0);
            params.put("incomingMax", 10000);
            params.put("outgoingMin", 0);
            params.put("outgoingMax", 10000);

            String cypher = "MATCH (n:" + db + ")-[r:Call]->(m) ";
            cypher = cypher + "WHERE n.carrier =~ {rnCode} AND m.carrier =~ {rnCode} AND ";
            cypher = cypher + "r.duration >= {durationMin} AND r.duration <= {durationMax} AND r.callDay =~ {callDay}  AND "
                    + "r.startTime >= {startTime} AND r.startTime <= {endTime} AND r.startDate >= {startDate} AND r.startDate <= {endDate} ";
            cypher = cypher + "RETURN ID(n) as nnid,n.number, n.age, n.gender, n.incoming, n.outgoing, n.promotion, n.carrier, n.arpu, "
                    + "ID(m) as mmid, m.number, m.age, m.gender, m.incoming, m.outgoing, m.promotion, m.carrier, m.arpu,"
                    + "r.duration, r.startDate, r.startTime, r.callDay";

            try (Transaction tx = graphDb.beginTx();
                    Result result = graphDb.execute(cypher, params)) {
                tx.success();
                while (result.hasNext()) {
                    Map<String, Object> row = result.next();

                    // Hashmap for storing attributes
                    Map<String, Object> a = new HashMap<>();
                    Map<String, Object> b = new HashMap<>();
                    Map<String, Object> r = new HashMap<>();

                    // Get all attribues returned from executing cypher
                    // Note : Returning order is uncontrollable;
                    for (Entry<String, Object> column : row.entrySet()) {

                        String domain = column.getKey().substring(0, 1);
                        String attr = column.getKey().substring(2);

                        if (domain.equals("n")) {
                            a.put(attr, column.getValue());
                        } else if (domain.equals("m")) {
                            b.put(attr, column.getValue());
                        } else if (domain.equals("r")) {
                            r.put(attr, column.getValue());
                        } else {
                            // This should not be triggered
                            throw new Exception("Invalid Cypher Return : Load All Function");
                        }
                    }

                    int aid = Integer.parseInt(a.get("id").toString()),
                            bid = Integer.parseInt(b.get("id").toString());
                    
                    
                    
                    Node caller = new Node(aid);
                    Node callee = new Node(bid);
                    caller.setLabel(a.get("number").toString());
                    callee.setLabel(b.get("number").toString());
                    
                    Edge rel = new Edge(
                            aid,
                            bid,
                            Integer.parseInt(r.get("duration").toString()),
                            Long.toString(Double.valueOf(r.get("startDate").toString()).longValue()),
                            r.get("startTime").toString(),
                            r.get("callDay").toString(),
                            Integer.parseInt(r.get("duration").toString())
                    );
                    
                    a.remove("id");
                    b.remove("id");
                    a.remove("number");
                    b.remove("number");

                    caller.setProperties(a);
                    callee.setProperties(b);
                    nodes.add(caller);
                    nodes.add(callee);
                    edges.add(rel);
                }
                return new com.seniorproject.graphmodule.Graph(nodes, edges);
            } catch (Exception e) {
                e.printStackTrace();
            }
        } finally {
            System.out.println("Graph is shut down successfully");
            graphDb.shutdown();
        }
        return null;
    }

    public void store(NodeIterable nodes, List<Edge> edges, String tid) throws IOException {
        Map<Integer, String> numberMapper = new HashMap<>();
        CSVWriter writer = new CSVWriter(new FileWriter("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/storage/tmp_migrate/processed_" + tid + "_profile.csv"), ',');
        for(Node n : nodes) {
            String[] line = n.splitPropertiesWithLabel();
            numberMapper.put(n.getID(), n.getLabel());
            writer.writeNext(line);
        }
        
        writer.close();
        
        
//        GraphDatabaseService gdb = new RestGraphDatabase("http://localhost:7474/db/data", "neo4j", "aiscu");
//        NodeIterable[] aNodes = NodeIterable.split(nodes, Config.THREAD_POOL);
//        StoringAgent[] sa = new StoringAgent[Config.THREAD_POOL];
//        for (int i = 0; i < Config.THREAD_POOL; i++) {
//            sa[i] = new StoringAgent(gdb, aNodes[i], "thread" + i);
//            sa[i].start();
//        }
    }

    public void storeCommunity(NodeIterable nodes, List<Edge> edges, String tid) {
        initDBConnection();
        try {
            Graph graph = Graph.create(dbAccess);
            Map<Integer, GrNode> grnodes = new HashMap<>();
            ITransaction tx = dbAccess.beginTX();
            int i = 0;

            for (Node n : nodes) {
                GrNode tmp = graph.createNode();
                tmp.addLabel("ProcessedCom" + tid);
                tmp.addProperty("Member", n.getProperty("member"));
                tmp.addProperty("Eccentricity", n.getProperty("eccentricity"));
                tmp.addProperty("Betweenness", n.getProperty("betweenness"));
                tmp.addProperty("Closeness", n.getProperty("closeness"));
                tmp.addProperty("CommunityID", n.getProperty("communityID"));
                tmp.addProperty("Color", n.getProperty("color"));
                grnodes.put(n.getID(), tmp);
            }

            for (Edge e : edges) {
                GrRelation rel = graph.createRelation("Call", grnodes.get(e.getSource()), grnodes.get(e.getTarget()));
                rel.addProperty("Duration", e.getDuration());
                rel.addProperty("StartDate", e.getStartDate());
                rel.addProperty("StartTime", e.getStartTime());
                rel.addProperty("CallDay", e.getCallDay());
            }
            List<JcError> errors = graph.store();
            tx.close();
            if (!errors.isEmpty()) {
                printErrors(errors);
            }
        } catch (Exception e) {

        } finally {
            closeDBConnection();
        }
    }

    private void closeDBConnection() {
        if (dbAccess != null) {
            dbAccess.close();
            dbAccess = null;
        }
    }

    /**
     * print errors to System.out
     *
     * @param result
     */
    private static void printErrors(JcQueryResult result) {
        StringBuilder sb = new StringBuilder();
        sb.append("---------------General Errors:");
        appendErrorList(result.getGeneralErrors(), sb);
        sb.append("\n---------------DB Errors:");
        appendErrorList(result.getDBErrors(), sb);
        sb.append("\n---------------end Errors:");
        String str = sb.toString();
        System.out.println("");
        System.out.println(str);
    }

    /**
     * print errors to System.out
     *
     * @param result
     */
    private static void printErrors(List<JcError> errors) {
        StringBuilder sb = new StringBuilder();
        sb.append("---------------Errors:");
        appendErrorList(errors, sb);
        sb.append("\n---------------end Errors:");
        String str = sb.toString();
        System.out.println("");
        System.out.println(str);
    }
}
