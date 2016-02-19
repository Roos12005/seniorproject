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
import org.neo4j.graphdb.Direction;
import org.neo4j.graphdb.DynamicLabel;
import org.neo4j.graphdb.GraphDatabaseService;
import org.neo4j.graphdb.Label;
import org.neo4j.graphdb.Relationship;
import org.neo4j.graphdb.ResourceIterator;
import org.neo4j.graphdb.Transaction;
import org.neo4j.graphdb.factory.GraphDatabaseFactory;

public class DBAccess {

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
            Map<String, List<Double>> fFilters) {

        Set<Node> nodes = new HashSet<>();
        List<Edge> edges = new ArrayList<>();

//        GraphDatabaseService graphDb = new GraphDatabaseFactory().newEmbeddedDatabase("/Users/pperfectionist/Documents/Neo4j/store.graphdb");
GraphDatabaseService graphDb = new GraphDatabaseFactory().newEmbeddedDatabase("/Applications/XAMPP/htdocs/seniorproject/database/Neo4j/store.graphdb");
//GraphDatabaseService graphDb = new GraphDatabaseFactory().newEmbeddedDatabase("C:/Users/thanp548/Documents/Neo4j/default.graphdb");
        Label label = DynamicLabel.label("Raw");
        try (Transaction tx = graphDb.beginTx();
                ResourceIterator<org.neo4j.graphdb.Node> customers = graphDb.findNodes(label)) {
            org.neo4j.graphdb.Node caller;

            while (customers.hasNext()) {
                caller = customers.next();

                for (Relationship rel : caller.getRelationships(Direction.OUTGOING)) {
                    org.neo4j.graphdb.Node callee = rel.getOtherNode(caller);
                    // Filter here

                    Map<String, Object> callerProps = caller.getAllProperties();
                    Map<String, Object> calleeProps = callee.getAllProperties();
                    Map<String, Object> relProps = rel.getAllProperties();
//                           if(callerProps.get("rnCode").toString().matches(toRegex(sFilters.get("rnCode"))) &&
//                                   Integer.parseInt(relProps.get("duration").toString()) < fFilters.get("duration").get(0) &&
//                                   Integer.parseInt(relProps.get("duration").toString()) > fFilters.get("duration").get(1) &&
//                                   Integer.parseInt(relProps.get("startDate").toString()) < fFilters.get("startDate").get(0) &&
//                                   Integer.parseInt(relProps.get("startDate").toString()) > fFilters.get("startDate").get(1) &&
//                                   Double.parseDouble(relProps.get("startTime").toString()) < fFilters.get("startTime").get(0) &&
//                                   Double.parseDouble(relProps.get("startTime").toString()) > fFilters.get("startTime").get(1) &&
//                                   relProps.get("startTime").toString().matches(toRegex(sFilters.get("callDay"))) &&
//                                   calleeProps.get("rnCode").toString().matches(toRegex(sFilters.get("rnCode"))) ) {
                    Node a = new Node((int) caller.getId());
                    a.setAge("15");
                    a.setGender("Male");
                    a.setLabel(callerProps.get("number").toString());
//                               a.setNoOfCall(Integer.parseInt(callerProps.get("outgoing").toString()));
//                               a.setNoOfReceive(Integer.parseInt(callerProps.get("incoming").toString()));
                    a.setRnCode("AIS");
                    a.setPromotion("");
                    Node b = new Node((int) callee.getId());
                    b.setAge("15");
                    b.setGender("Male");
                    b.setLabel(calleeProps.get("number").toString());
//                               b.setNoOfCall(Integer.parseInt(calleeProps.get("outgoing").toString()));
//                               b.setNoOfReceive(Integer.parseInt(calleeProps.get("incoming").toString()));
                    b.setRnCode("AIS");
                    b.setPromotion("");

                    Edge r = new Edge(
                            a.getID(),
                            b.getID(),
                            1,
                            //                                       Integer.parseInt(relProps.get("duration").toString()), // weight
                            relProps.get("startDate").toString(),
                            relProps.get("startTime").toString(),
                            relProps.get("callDay").toString(),
                            1
                    //                                       Integer.parseInt(relProps.get("duration").toString())
                    );

                    nodes.add(a);
                    nodes.add(b);
                    edges.add(r);
//                     }

                }
            }
            System.out.println("Reading Graph is Done");
            customers.close();
            return new com.seniorproject.graphmodule.Graph(nodes, edges);
        } finally {
            graphDb.shutdown();
        }
    }

        public void store(NodeIterable nodes, List<Edge> edges, String tid) {
        initDBConnection();
        try {
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
                tmp.addProperty("NoOfCall", n.getNoOfCall());
                tmp.addProperty("NoOfReceive", n.getNoOfReceive());

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
}
