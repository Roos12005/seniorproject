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
import iot.jcypher.graph.GrProperty;
import iot.jcypher.graph.GrRelation;
import iot.jcypher.graph.Graph;
import iot.jcypher.query.JcQuery;
import iot.jcypher.query.JcQueryResult;
import iot.jcypher.query.api.IClause;
import iot.jcypher.query.api.predicate.Concat;
import iot.jcypher.query.api.predicate.Concatenator;
import iot.jcypher.query.factories.clause.MATCH;
import iot.jcypher.query.factories.clause.RETURN;
import iot.jcypher.query.factories.clause.WHERE;
import iot.jcypher.query.factories.clause.WITH;
import iot.jcypher.query.factories.xpression.C;
import iot.jcypher.query.result.JcError;
import iot.jcypher.query.values.JcCollection;
import iot.jcypher.query.values.JcNode;
import iot.jcypher.query.values.JcNumber;
import iot.jcypher.query.values.JcRelation;
import iot.jcypher.query.values.JcValue;
import iot.jcypher.query.writer.Format;
import iot.jcypher.util.Util;
import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Map.Entry;
import java.util.Properties;
import java.util.Set;

/**
 *
 * @author pperfectionist
 */
public class DBAccess {

    private static IDBAccess dbAccess;

    public DBAccess() {
        
    }

    public static void clearGraph(String start, String end, String relationship) {
        JcQuery query = new JcQuery();
        JcNode a = new JcNode("A");
        JcNode b = new JcNode("B");
        JcRelation r = new JcRelation("Call");
        query.setClauses(new IClause[]{
            MATCH.node(a).label(start).relation(r).type(relationship).out().node(b).label(end),
            RETURN.value(r),
            RETURN.value(a),
            RETURN.value(b)
        });
        JcQueryResult result = dbAccess.execute(query); 
        List<GrNode> aNode = result.resultOf(a);
        for(GrNode tmp : aNode) {
            tmp.remove();
        }
        List<GrNode> bNode = result.resultOf(b);
        for(GrNode tmp : bNode) {
            tmp.remove();
        }
        List<GrRelation> rela = result.resultOf(r);
        for(GrRelation tmp : rela) {
            tmp.remove();
        }
        
        // retrieve the Graph (container of the graph model)
        Graph graph = result.getGraph();

        // store the modified graph
        List<JcError> errors = graph.store();

    }
    
    private static void initDBConnection() {
        Properties props = new Properties();

        // properties for remote access and for embedded access
        // (not needed for in memory access)
        // have a look at the DBProperties interface
        // the appropriate database access class will pick the properties it needs
        props.setProperty(DBProperties.SERVER_ROOT_URI, Config.HOST_NAME);

        /**
         * connect to remote database via REST (SERVER_ROOT_URI property is
         * needed)
         */
        dbAccess = DBAccessFactory.createDBAccess(DBType.REMOTE, props, Config.USERNAME, Config.PASSWORD);
    }
   
    
    public com.seniorproject.graphmodule.Graph loadAll(Map<String, List<String>> sFilters,
            Map<String, List<Double>> fFilters) {
        initDBConnection();
        Set<Node> nodes = new HashSet<>();
        List<Edge> edges = new ArrayList<>();
        try {
            JcQuery query = new JcQuery();
            JcNode a = new JcNode("A");
            JcNode b = new JcNode("B");
            JcRelation r = new JcRelation("Call");
            
            int j=0;
            Concat con = WHERE.valueOf(r.property("duration")).GTE(-1).AND();
            for(Entry<String, List<Double>> entry : fFilters.entrySet()) {
                String key = entry.getKey();
                List<Double> value = entry.getValue();
                  con = con.valueOf(r.property(key)).GTE(value.get(0)).AND();
                  con = con.valueOf(r.property(key)).LTE(value.get(1)).AND();     
                System.out.println(key + " ---- " + value.get(0) + " / " + value.get(1));
            }
//            System.out.println("---------- S Filters ----------");
            for(Entry<String, List<String>> entry : sFilters.entrySet()) {
                String key = entry.getKey();
                List<String> value = entry.getValue();
                
                String tmpRegex = value.get(0);
                value.remove(0);
                for(String tmp : value) {
                    tmpRegex = tmpRegex + "|" + tmp;
                }
                con = con.BR_OPEN().valueOf(r.property(key)).REGEX(tmpRegex).OR()
                        .BR_OPEN().valueOf(a.property(key)).REGEX(tmpRegex).AND()
                        .valueOf(b.property(key)).REGEX(tmpRegex).BR_CLOSE().BR_CLOSE().AND();
                
                System.out.println(key + " ---- " + tmpRegex);
            }
            query.setClauses(new IClause[]{
                MATCH.node(a).label("Node").relation(r).out().node(b).label("Node"),
                con.valueOf(r.property("duration")).GTE(0),
                RETURN.value(r),
            });

//            
//            icList.add(RETURN.value(r));
//            IClause[] iclauses = new IClause[icList.size()];
//            icList.toArray(iclauses);
//            query.setClauses(iclauses);
//            Concat ccc = WHERE.valueOf(r.property("startDate")).GTE(ran[4]).AND();
//            ccc.valueOf(r.property("startDate")).LTE(ran[5]);
            
//            System.out.println(ran[0] + " " + ran[1] + " " + ran[4] + " " + ran[5]);
//            query.setClauses(new IClause[]{
//                MATCH.node(a).label("Node").relation(r).out().node(b).label("Node"),
//                ccc.valueOf(r.property("duration")).GT(0),
//                WHERE.valueOf(r.property("startDate")).GTE(ran[4]),
//                    WHERE.valueOf(r.property("startDate")).LTE(ran[5]),
//                    WHERE.valueOf(r.property("duration")).GTE(ran[0]),
//                    WHERE.valueOf(r.property("duration")).LTE(ran[1]),
//                    WHERE.valueOf(r.property("startTime")).GTE(ran[2]),
//                    WHERE.valueOf(r.property("startTime")).LTE(ran[3]),
//                    WHERE.valueOf(r.property("callDay")).REGEX(rex[0]),
//                    WHERE.valueOf(a.property("rnCode")).REGEX(rex[1]),
//                RETURN.value(r),
//            });
            
            JcQueryResult result = dbAccess.execute(query);
            System.out.println(result.resultOf(r).getClass().getName());
            List<GrRelation> relationships = result.resultOf(r);
            for(GrRelation gr : relationships) {
                GrNode sNode = gr.getStartNode();
                GrNode eNode = gr.getEndNode();

                System.out.println(sNode.getId());
                System.out.println(eNode.getId());

                List<GrProperty> sProps = sNode.getProperties();
                List<GrProperty> eProps = eNode.getProperties();
                List<GrProperty> rProps = gr.getProperties();
                

                int startNodeID = (int) sNode.getId();
                int endNodeID = (int) eNode.getId();
                int duration = 0;
                float weight = 1.0f;
                String startDate = "";
                String startTime = "";
                String callDay = "";
                
                Node aNode = new Node(startNodeID);
                Node bNode = new Node(endNodeID);
                
                
                for(GrProperty gp : sProps) {
                    if(gp.getName().equals("number")) {
                        aNode.setLabel(gp.getValue().toString());
                    }
                    if(gp.getName().equals("age")) {
                        aNode.setAge(gp.getValue().toString());
                    }
                    if(gp.getName().equals("gender")) {
                        aNode.setGender(gp.getValue().toString());
                    }
                    if(gp.getName().equals("rnCode")) {
                        aNode.setRnCode(gp.getValue().toString());
                    }
                    if(gp.getName().equals("promotion")) {
                        aNode.setPromotion(gp.getValue().toString());
                    }
                }
                
                for(GrProperty gp : eProps) {
                    if(gp.getName().equals("number")) {
                        bNode.setLabel(gp.getValue().toString());
                    }
                    if(gp.getName().equals("age")) {
                        bNode.setAge(gp.getValue().toString());
                    }
                    if(gp.getName().equals("gender")) {
                        bNode.setGender(gp.getValue().toString());
                    }
                    if(gp.getName().equals("rnCode")) {
                        bNode.setRnCode(gp.getValue().toString());
                    }
                    if(gp.getName().equals("promotion")) {
                        bNode.setPromotion(gp.getValue().toString());
                    }
                }
                
                for(GrProperty gp : rProps) {
                    if(gp.getName().equals("duration")) {
                        duration = Integer.parseInt(gp.getValue().toString());
                    }
                    if(gp.getName().equals("startDate")) {
                        startDate = gp.getValue().toString();
                    }
                    if(gp.getName().equals("startTime")) {
                        startTime = gp.getValue().toString();
                    }
                    if(gp.getName().equals("callDay")) {
                        callDay = gp.getValue().toString();
                    }

                }
                
                nodes.add(aNode);
                nodes.add(bNode);
                
                edges.add(new Edge(startNodeID, endNodeID, weight , startDate, startTime, callDay, duration));   
            }
            
            return new com.seniorproject.graphmodule.Graph(nodes, edges);
        } catch (Exception e) {
            e.printStackTrace();
        } finally {
            closeDBConnection();
        }
        return null;
    }
    
    public void store(NodeIterable nodes, List<Edge> edges) {
        initDBConnection();
        try {
            Graph graph = Graph.create(dbAccess);
            
            clearGraph("User", "User", "Call");
            
            Map<Integer, GrNode> grnodes = new HashMap<>();
            for(Node n : nodes) {
                GrNode tmp = graph.createNode();
                tmp.addLabel("User");
                tmp.addProperty("Number", n.getLabel());
                tmp.addProperty("Eccentricity", n.getEccentricity());
                tmp.addProperty("Betweenness", n.getBetweenness());
                tmp.addProperty("Closeness", n.getCloseness());
                tmp.addProperty("CommunityID", n.getCommunityID());
                tmp.addProperty("Age", n.getAge());
                tmp.addProperty("Gender", n.getGender());
                tmp.addProperty("RnCode", n.getRnCode());
                tmp.addProperty("Promotion", n.getPromotion());

                tmp.addProperty("Color", n.getColor());
                grnodes.put(n.getID(), tmp);
            }
            
            for(Edge e : edges) {
                GrRelation rel = graph.createRelation("Call", grnodes.get(e.getSource()), grnodes.get(e.getTarget()));
                rel.addProperty("Duration", e.getDuration());
                rel.addProperty("StartDate", e.getStartDate());
                rel.addProperty("StartTime", e.getStartTime());
                rel.addProperty("CallDay", e.getCallDay());
            }
            
            List<JcError> errors = graph.store();
            if (!errors.isEmpty())
                    printErrors(errors);
        } catch (Exception e) {

        } finally {
            closeDBConnection();
        }
    }

    public void storeCommunity(NodeIterable nodes, List<Edge> edges) {
        initDBConnection();
        try {
            Graph graph = Graph.create(dbAccess);
            clearGraph("User_Com", "User_Com", "Call");
            Map<Integer, GrNode> grnodes = new HashMap<>();
            for(Node n : nodes) {
                GrNode tmp = graph.createNode();
                tmp.addLabel("User_Com");
                tmp.addProperty("Member", n.getMember());
                tmp.addProperty("Eccentricity", n.getEccentricity());
                tmp.addProperty("Betweenness", n.getBetweenness());
                tmp.addProperty("Closeness", n.getCloseness());
                tmp.addProperty("CommunityID", n.getCommunityID());
                tmp.addProperty("Color", n.getColor());
                grnodes.put(n.getID(), tmp);
            }
            
            for(Edge e : edges) {
                GrRelation rel = graph.createRelation("Call", grnodes.get(e.getSource()), grnodes.get(e.getTarget()));
                rel.addProperty("Duration", e.getDuration());
                rel.addProperty("StartDate", e.getStartDate());
                rel.addProperty("StartTime", e.getStartTime());
                rel.addProperty("CallDay", e.getCallDay());
            }
            List<JcError> errors = graph.store();
            if (!errors.isEmpty())
                    printErrors(errors);
        } catch (Exception e) {
            e.printStackTrace();
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

    private static void appendErrorList(List<JcError> errors, StringBuilder sb) {
        int num = errors.size();
        for (int i = 0; i < num; i++) {
            JcError err = errors.get(i);
            sb.append('\n');
            if (i > 0) {
                sb.append("-------------------\n");
            }
            sb.append("codeOrType: ");
            sb.append(err.getCodeOrType());
            sb.append("\nmessage: ");
            sb.append(err.getMessage());
            if (err.getAdditionalInfo() != null) {
                sb.append("\nadditional info: ");
                sb.append(err.getAdditionalInfo());
            }
        }
    }

}
