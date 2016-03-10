/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.storingmodule;

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

        for(String rnCode : sFilters.get("rnCode")) {
            rnCode_Regex = rnCode_Regex + rnCode + "|";
        }
        for(String callDay : sFilters.get("callDay")) {
            callDay_Regex = callDay_Regex + callDay + "|";
        }
     
        GraphDatabaseService graphDb = new GraphDatabaseFactory().newEmbeddedDatabase("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/database/Neo4j/store.graphdb");
        
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
        
        
        String cypher = "MATCH (n:" + db + ")-[r:Call]->(m)";
        cypher = cypher + "WHERE n.rnCode =~ {rnCode} AND m.rnCode =~ {rnCode} AND";
        cypher = cypher + "r.duration >= {durationMin} AND r.duration <= {durationMax} AND callDay =~ {callDay}"
                + "r.startTime >= {startTime} AND r.startTime <= {endTime} AND r.startDate >= {startDate} AND r.startDate <= {endDate}";
        cypher = cypher + "RETURN n,m,r";
        try ( Transaction tx = graphDb.beginTx();
                Result result = graphDb.execute( cypher , params) ) {
                tx.success();
              while ( result.hasNext() ) {
                  Map<String,Object> row = result.next();
                  for ( Entry<String,Object> column : row.entrySet() ) {
//                      rows += column.getKey() + ": " + column.getValue() + "; ";
//                      System.out.println(column.getKey() + ": " + column.getValue() + "; ");
                  }
//                  rows += "\n";
              }
              
          } catch (Exception e) {
              e.printStackTrace();
          }finally {
            System.out.println("Graph is shut down successfully");
            graphDb.shutdown();
        }
        return null;



//GraphDatabaseService graphDb = new GraphDatabaseFactory().newEmbeddedDatabase("C:/Users/thanp548/Documents/Neo4j/default.graphdb");
//        Label label = DynamicLabel.label(db);
//        try (Transaction tx = graphDb.beginTx();
//                ResourceIterator<org.neo4j.graphdb.Node> customers = graphDb.findNodes(label)) {
//            org.neo4j.graphdb.Node caller;
//
//            while (customers.hasNext()) {
//                caller = customers.next();
//
//                for (Relationship rel : caller.getRelationships(Direction.OUTGOING)) {
//                    org.neo4j.graphdb.Node callee = rel.getOtherNode(caller);
//
//                    // Filter here
//                    Map<String, Object> callerProps = caller.getAllProperties();
//                    Map<String, Object> calleeProps = callee.getAllProperties();
//                    Map<String, Object> relProps = rel.getAllProperties();
//
//                    if(Integer.parseInt(relProps.get("duration").toString()) > fFilters.get("duration").get(0) &&
//                        Integer.parseInt(relProps.get("duration").toString()) < fFilters.get("duration").get(1) &&
//                        Double.parseDouble(relProps.get("startTime").toString()) > fFilters.get("startTime").get(0) &&
//                        Double.parseDouble(relProps.get("startTime").toString()) < fFilters.get("startTime").get(1) &&
//                        callerProps.get("rnCode").toString().matches(rnCode_Regex) &&
//                        calleeProps.get("rnCode").toString().matches(rnCode_Regex) &&
//                        relProps.get("callDay").toString().matches(callDay_Regex) &&
//                        Double.parseDouble(relProps.get("startDate").toString()) > fFilters.get("startDate").get(0) &&
//                        Double.parseDouble(relProps.get("startDate").toString()) < fFilters.get("startDate").get(1) ) {
//
//                        Node a = new Node((int) caller.getId());
////                        a.setAge(callerProps.get("age").toString());
////                        a.setGender(callerProps.get("gender").toString());
//                        a.setLabel(callerProps.get("number").toString());
//                        a.setNoOfOutgoing(Integer.parseInt(callerProps.get("outgoing").toString()));
//                        a.setNoOfIncoming(Integer.parseInt(callerProps.get("incoming").toString()));
//                        a.setRnCode(callerProps.get("rnCode").toString());
////                        a.setPromotion(callerProps.get("promotion").toString());
//
//                        Node b = new Node((int) callee.getId());
////                        b.setAge(calleeProps.get("age").toString());
////                        b.setGender(calleeProps.get("gender").toString());
//                        b.setLabel(calleeProps.get("number").toString());
//                        b.setNoOfOutgoing(Integer.parseInt(calleeProps.get("outgoing").toString()));
//                        b.setNoOfIncoming(Integer.parseInt(calleeProps.get("incoming").toString()));
//                        b.setRnCode(calleeProps.get("rnCode").toString());
////                        b.setPromotion(calleeProps.get("promotion").toString());
//
//                        Edge r = new Edge(
//                                a.getID(),
//                                b.getID(),
//                                1,
//                                //Integer.parseInt(relProps.get("duration").toString()), // weight
//                                Long.toString(Double.valueOf(relProps.get("startDate").toString()).longValue()),
//                                relProps.get("startTime").toString(),
//                                relProps.get("callDay").toString(),
//                                Integer.parseInt(relProps.get("duration").toString())
//                        );
//
//                        nodes.add(a);
//                        nodes.add(b);
//                        edges.add(r);
//                    }
//
//                }
//            }
//            System.out.println("Reading Graph is Done");
//            customers.close();
//            return new com.seniorproject.graphmodule.Graph(nodes, edges);
//        } finally {
//            graphDb.shutdown();
//        }
    }

        public void store(NodeIterable nodes, List<Edge> edges, String tid) {
//        initDBConnection();
//        try {
//            Graph graph = Graph.create(dbAccess);
//            Map<Integer, GrNode> grnodes = new HashMap<>();
//            ITransaction tx = dbAccess.beginTX();
//            int i=0;
//            for (Node n : nodes) {
//                GrNode tmp = graph.createNode();
//                tmp.addLabel("Processed" + tid);
//                tmp.addProperty("Number", n.getLabel());
//                tmp.addProperty("Eccentricity", n.getEccentricity());
//                tmp.addProperty("Betweenness", n.getBetweenness());
//                tmp.addProperty("Closeness", n.getCloseness());
//                tmp.addProperty("CommunityID", n.getCommunityID());
//                tmp.addProperty("Age", n.getAge());
//                tmp.addProperty("Gender", n.getGender());
//                tmp.addProperty("RnCode", n.getRnCode());
//                tmp.addProperty("Promotion", n.getPromotion());
//                tmp.addProperty("NoOfOutgoing", n.getNoOfOutgoing());
//                tmp.addProperty("NoOfIncoming", n.getNoOfIncoming());
//
//                tmp.addProperty("Color", n.getColor());
//                grnodes.put(n.getID(), tmp);
//            }
//                
//            for (Edge e : edges) {
//                GrRelation rel = graph.createRelation("Call", grnodes.get(e.getSource()), grnodes.get(e.getTarget()));
//                rel.addProperty("Duration", e.getDuration());
//                rel.addProperty("StartDate", e.getStartDate());
//                rel.addProperty("StartTime", e.getStartTime());
//                rel.addProperty("CallDay", e.getCallDay());
//            }
//            List<JcError> errors = graph.store();
//            tx.close();
//            if (!errors.isEmpty()) {
//                printErrors(errors);
//            }
//        } catch (Exception e) {
//
//        } finally {
//            closeDBConnection();
//        }
        
        
        GraphDatabaseService gdb = new RestGraphDatabase("http://localhost:7474/db/data", "neo4j", "aiscu");   
        NodeIterable[] aNodes = NodeIterable.split(nodes, Config.THREAD_POOL);
        StoringAgent[] sa = new StoringAgent[Config.THREAD_POOL];
        for(int i=0;i<Config.THREAD_POOL; i++) {
            sa[i] = new StoringAgent(gdb, aNodes[i], "thread" + i);
            sa[i].start();
        }
    }

    public void storeCommunity(NodeIterable nodes, List<Edge> edges, String tid) {
        initDBConnection();
        try {
            Graph graph = Graph.create(dbAccess);
            Map<Integer, GrNode> grnodes = new HashMap<>();
            ITransaction tx = dbAccess.beginTX();
            int i=0;
            
            for (Node n : nodes) {
                GrNode tmp = graph.createNode();
                tmp.addLabel("ProcessedCom" + tid);
                tmp.addProperty("Member", n.getMember());
                tmp.addProperty("Eccentricity", n.getEccentricity());
                tmp.addProperty("Betweenness", n.getBetweenness());
                tmp.addProperty("Closeness", n.getCloseness());
                tmp.addProperty("CommunityID", n.getCommunityID());
                tmp.addProperty("Color", n.getColor());
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
    class InsertTask implements Runnable {
        
        private NodeIterable nodes = null;
        private EdgeIterable edges = null;
        private String tid;
        public InsertTask(NodeIterable nodes, EdgeIterable edges, String tid) {
            this.nodes = nodes;
            this.edges = edges;
            this.tid = tid;
        }
        
        @Override
        public void run() {
            try {
                // do something
            Graph graph = Graph.create(dbAccess);
            Map<Integer, GrNode> grnodes = new HashMap<>();
            ITransaction tx = dbAccess.beginTX();
            int i=0;
            for (Node n : nodes) {
                GrNode tmp = graph.createNode();
                tmp.addLabel("Processed" + tid);
                tmp.addProperty("Number", n.getLabel());
                tmp.addProperty("Eccentricity", n.getEccentricity());
                tmp.addProperty("Betweenness", n.getBetweenness());
                tmp.addProperty("Closeness", n.getCloseness());
                tmp.addProperty("CommunityID", n.getCommunityID());
                tmp.addProperty("Age", n.getAge());
                tmp.addProperty("Gender", n.getGender());
                tmp.addProperty("RnCode", n.getRnCode());
                tmp.addProperty("Promotion", n.getPromotion());
                tmp.addProperty("NoOfOutgoing", n.getNoOfOutgoing());
                tmp.addProperty("NoOfIncoming", n.getNoOfIncoming());

                tmp.addProperty("Color", n.getColor());
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
            } finally {
                closeDBConnection();
                semaphore.release();
            }
        }
    } 
}
