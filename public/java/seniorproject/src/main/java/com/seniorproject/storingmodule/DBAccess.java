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
import java.text.DecimalFormat;

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

        String rnCode_Regex = "";
        String callDay_Regex = "";

        for(String rnCode : sFilters.get("rnCode")) {
            rnCode_Regex = rnCode_Regex + rnCode + "|";
        }
        for(String callDay : sFilters.get("callDay")) {
            callDay_Regex = callDay_Regex + callDay + "|";
        }

        GraphDatabaseService graphDb = new GraphDatabaseFactory().newEmbeddedDatabase("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/database/Neo4j/store.graphdb");
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

                    if(Integer.parseInt(relProps.get("duration").toString()) > fFilters.get("duration").get(0) &&
                        Integer.parseInt(relProps.get("duration").toString()) < fFilters.get("duration").get(1) &&
                        Double.parseDouble(relProps.get("startTime").toString()) > fFilters.get("startTime").get(0) &&
                        Double.parseDouble(relProps.get("startTime").toString()) < fFilters.get("startTime").get(1) &&
                        callerProps.get("carrier").toString().matches(rnCode_Regex) &&
                        calleeProps.get("carrier").toString().matches(rnCode_Regex) &&
                        relProps.get("callDay").toString().matches(callDay_Regex) &&
                        Double.parseDouble(relProps.get("startDate").toString()) > fFilters.get("startDate").get(0) &&
                        Double.parseDouble(relProps.get("startDate").toString()) < fFilters.get("startDate").get(1) ) {

                        Node a = new Node((int) caller.getId());
                        a.setAge(callerProps.get("age").toString());
                        a.setGender(callerProps.get("gender").toString());
                        a.setLabel(callerProps.get("number").toString());
                        a.setNoOfOutgoing(Integer.parseInt(callerProps.get("outgoing").toString()));
                        a.setNoOfIncoming(Integer.parseInt(callerProps.get("incoming").toString()));
                        a.setCarrier(callerProps.get("carrier").toString());
                        a.setArpu(callerProps.get("arpu").toString());
                        a.setPromotion(callerProps.get("promotion").toString());

                        Node b = new Node((int) callee.getId());
                        b.setAge(calleeProps.get("age").toString());
                        b.setGender(calleeProps.get("gender").toString());
                        b.setLabel(calleeProps.get("number").toString());
                        b.setNoOfOutgoing(Integer.parseInt(calleeProps.get("outgoing").toString()));
                        b.setNoOfIncoming(Integer.parseInt(calleeProps.get("incoming").toString()));
                        b.setCarrier(calleeProps.get("carrier").toString());
                        b.setArpu(calleeProps.get("arpu").toString());
                        b.setPromotion(calleeProps.get("promotion").toString());

                        Edge r = new Edge(
                                a.getID(),
                                b.getID(),
                                1,
                                //Integer.parseInt(relProps.get("duration").toString()), // weight
                                Long.toString(Double.valueOf(relProps.get("startDate").toString()).longValue()),
                                relProps.get("startTime").toString(),
                                relProps.get("callDay").toString(),
                                Integer.parseInt(relProps.get("duration").toString()),
                                calleeProps.get("carrier").toString()
                        );

                        nodes.add(a);
                        nodes.add(b);
                        edges.add(r);
                    }

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
                tmp.addProperty("Carrier", n.getCarrier());
                tmp.addProperty("Arpu", n.getArpu());
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

    public void storeCommunity(NodeIterable nodes, List<Edge> edges, int[] comMember, int[]comArpu, int[] comAis, int[] comCallOtherCarrier, int[] comDaytimeCall, int[] comNighttimeCall, int[] comWeekdayCall, int[] comWeekendCall, int[] comInGroupCall, int[] comOutGroupCall, int[] comDurationCall, String tid) {
        initDBConnection();
        try {
            Graph graph = Graph.create(dbAccess);
            Map<Integer, GrNode> grnodes = new HashMap<>();
            ITransaction tx = dbAccess.beginTX();
            DecimalFormat df = new DecimalFormat("#.##");

            int num_community = comMember.length;

            /*
            0 = member                 1 = averageArpu             2 = aisRatio
            3 = call other carrier     4 = duration                5 = no. of call
            */

            double[] min = new double[6];
            double[] max = new double[6];

            for(int i = 0; i < 6; i++){
                min[i] = Integer.MAX_VALUE;
            }

            //find min & max of each profiles 
            for(int i = 0; i < num_community; i++){
                if(comMember[i] < min[0]) min[0] = comMember[i];
                if(comMember[i] > max[0]) max[0] = comMember[i];
                if((double)comArpu[i]/(double)comAis[i] < min[1]) min[1] = (double)comArpu[i]/(double)comAis[i];
                if((double)comArpu[i]/(double)comAis[i] > max[1]) max[1] = (double)comArpu[i]/(double)comAis[i];
                if((double)comAis[i]/(double)comMember[i] < min[2]) min[2] = (double)comAis[i]/(double)comMember[i];
                if((double)comAis[i]/(double)comMember[i] > max[2]) max[2] = (double)comAis[i]/(double)comMember[i];
                if((double)comCallOtherCarrier[i]/(double)(comDaytimeCall[i] + comNighttimeCall[i]) < min[3]) min[3] = (double)comCallOtherCarrier[i]/(double)(comDaytimeCall[i] + comNighttimeCall[i]);
                if((double)comCallOtherCarrier[i]/(double)(comDaytimeCall[i] + comNighttimeCall[i]) > max[3]) max[3] = (double)comCallOtherCarrier[i]/(double)(comDaytimeCall[i] + comNighttimeCall[i]);
                if((double)comDurationCall[i]/(comDaytimeCall[i]+comNighttimeCall[i]) < min[4]) min[4] = (double)comDurationCall[i]/(comDaytimeCall[i]+comNighttimeCall[i]);
                if((double)comDurationCall[i]/(comDaytimeCall[i]+comNighttimeCall[i]) > max[4]) max[4] = (double)comDurationCall[i]/(comDaytimeCall[i]+comNighttimeCall[i]);
                if((double)(comDaytimeCall[i]+comNighttimeCall[i])/comAis[i] < min[5]) min[5] = (double)(comDaytimeCall[i]+comNighttimeCall[i])/comAis[i];
                if((double)(comDaytimeCall[i]+comNighttimeCall[i])/comAis[i] > max[5]) max[5] = (double)(comDaytimeCall[i]+comNighttimeCall[i])/comAis[i];
            }
            
            for (Node n : nodes) {
                GrNode tmp = graph.createNode();
                tmp.addLabel("ProcessedCom" + tid);
                tmp.addProperty("Member", n.getMember());
                tmp.addProperty("Eccentricity", n.getEccentricity());
                tmp.addProperty("Betweenness", n.getBetweenness());
                tmp.addProperty("Closeness", n.getCloseness());
                tmp.addProperty("CommunityID", n.getCommunityID());
                tmp.addProperty("Color", n.getColor());

                //community profile attrbutes
                tmp.addProperty("AverageArpu",df.format((double)comArpu[n.getCommunityID()]/(double)comAis[n.getCommunityID()]));
                tmp.addProperty("AisRatio",df.format((double)comAis[n.getCommunityID()]/(double)n.getMember()));
                tmp.addProperty("CallOtherCarrierRatio",df.format((double)comCallOtherCarrier[n.getCommunityID()]/(double)(comDaytimeCall[n.getCommunityID()] + comNighttimeCall[n.getCommunityID()])));
                tmp.addProperty("DaytimeCall",comDaytimeCall[n.getCommunityID()]);
                tmp.addProperty("NighttimeCall",comNighttimeCall[n.getCommunityID()]);
                tmp.addProperty("WeekdayCall",comWeekdayCall[n.getCommunityID()]);
                tmp.addProperty("WeekendCall",comWeekendCall[n.getCommunityID()]);
                tmp.addProperty("InGroupCall",comInGroupCall[n.getCommunityID()]);
                tmp.addProperty("OutGroupCall",comOutGroupCall[n.getCommunityID()]);
                tmp.addProperty("AverageNoOfCall",df.format((double)(comDaytimeCall[n.getCommunityID()]+comNighttimeCall[n.getCommunityID()])/comAis[n.getCommunityID()]));
                tmp.addProperty("AverageDurationCall",df.format((double)comDurationCall[n.getCommunityID()]/(comDaytimeCall[n.getCommunityID()]+comNighttimeCall[n.getCommunityID()])));

                //community profile group
                tmp.addProperty("MemberProfile",mapLevel(comMember[n.getCommunityID()],min[0],max[0]));
                tmp.addProperty("AverageArpuProfile",mapLevel((double)comArpu[n.getCommunityID()]/(double)comAis[n.getCommunityID()],min[1],max[1]));
                tmp.addProperty("AisRatioProfile",mapLevel((double)comAis[n.getCommunityID()]/(double)comMember[n.getCommunityID()],min[2],max[2]));
                tmp.addProperty("CallOtherCarrierProfile",mapLevel((double)comCallOtherCarrier[n.getCommunityID()]/(double)(comDaytimeCall[n.getCommunityID()] + comNighttimeCall[n.getCommunityID()]),min[3],max[3]));
                tmp.addProperty("AverageDurationProfile",mapLevel((double)comDurationCall[n.getCommunityID()]/(comDaytimeCall[n.getCommunityID()]+comNighttimeCall[n.getCommunityID()]),min[4],max[4]));
                tmp.addProperty("AverageNoOfCallProfile",mapLevel((double)(comDaytimeCall[n.getCommunityID()]+comNighttimeCall[n.getCommunityID()])/comAis[n.getCommunityID()],min[5],max[5]));
                if((comDaytimeCall[n.getCommunityID()] - comNighttimeCall[n.getCommunityID()]) < -5) tmp.addProperty("DaytimeNighttimeProfile","Nighttime");
                else if((comDaytimeCall[n.getCommunityID()] - comNighttimeCall[n.getCommunityID()]) > 5) tmp.addProperty("DaytimeNighttimeProfile","Daytime");
                else tmp.addProperty("DaytimeNighttimeProfile","Average");
                if(((double)comWeekdayCall[n.getCommunityID()]/5 - (double)comWeekendCall[n.getCommunityID()]/2) < -1) tmp.addProperty("WeekdayWeekendProfile","Weekend");
                else if(((double)comWeekdayCall[n.getCommunityID()]/5 - (double)comWeekendCall[n.getCommunityID()]/2) > 1) tmp.addProperty("WeekdayWeekendProfile","Weekday");
                else tmp.addProperty("WeekdayWeekendProfile","Average");

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

    private String mapLevel(double value, double min, double max) {
        double range = max - min;
        if(value < min + range/5) return "Very Low";
        else if(value < min + range*2/5) return "Low";
        else if(value < min + range*3/5) return "Medium";
        else if(value < min + range*4/5) return "High";
        else return "Very High";
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
