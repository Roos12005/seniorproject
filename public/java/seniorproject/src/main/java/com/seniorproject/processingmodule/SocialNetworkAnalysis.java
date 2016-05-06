package com.seniorproject.processingmodule;

import com.seniorproject.graphmodule.Edge;
import com.seniorproject.graphmodule.Graph;
import com.seniorproject.graphmodule.Node;
import com.seniorproject.graphmodule.NodeIterable;
import com.seniorproject.storingmodule.DBAccess;
import java.io.IOException;
import java.text.DecimalFormat;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;

public class SocialNetworkAnalysis {
    
    protected void foo() {
        
    }
    
    public static void extractFilters(String[] args, int startIdx, int endIdx, Map<String, List<String>> sFilters, Map<String, List<Double>> dFilters) {
        for(int i=startIdx; i<endIdx; i++) {
            String key = args[i++];
            int is_number = Integer.parseInt(args[i++]);
            int args_len = Integer.parseInt(args[i++]);

            if (is_number == 1) {
                List<Double> tmp = new ArrayList<>();
                for (int j = 0; j < args_len; j++, i++) {
                    tmp.add(Double.parseDouble(args[i]));
                }
                dFilters.put(key, tmp);
            } else {
                List<String> tmp = new ArrayList<>();
                for (int j = 0; j < args_len; j++, i++) {
                    tmp.add(args[i]);
                }
                sFilters.put(key, tmp);
            }
            i--;
        }
    }

    public static void main(String[] args) throws IOException {
        String tid = args[0];
        String database = args[1];
        System.out.println("Analyses Database : " + database);
        boolean comOfCom = args[2].equals("1");
        
        // ----------------------------- Extracting all Filters from Arguments --------------------------
        long startTime = System.currentTimeMillis();
        Map<String, List<Double>> comparableFilters = new HashMap<>();
        Map<String, List<String>> stringFilters = new HashMap<>();
        extractFilters(args, 3, args.length, stringFilters, comparableFilters);
        
        // ----------------------------- Customer Section -----------------------------------------
        // ----------------------------- Building Graph-like Structure in Java --------------------------
        Graph hgraph = (new DBAccess()).loadAll(stringFilters, comparableFilters, database);
        long buildGraphTime = System.currentTimeMillis();
        System.out.println("Building Graph ... Done! exec time : " + (buildGraphTime - startTime) + " ms");
        
        // ----------------------------- Calculating Betweenness and Closeness Centrality ---------------
        GraphDistance dis = new GraphDistance(hgraph);
        dis.execute(hgraph);
        long calTime = System.currentTimeMillis();
        System.out.println("Calculating Graph Distance ... Done! exec time : " + (calTime - buildGraphTime) + " ms");
        
        // ----------------------------- Detecting Customer Communities ---------------------------
        Modularity mod = new Modularity(hgraph);
        int totalCommunities = mod.buildCommunities(hgraph);
        long comTime = System.currentTimeMillis();
        System.out.println("Detecting Communities ... Done! exec time : " + (comTime - calTime) + " ms");
        System.out.println("Classifying customers into " + totalCommunities + " communities");

        // ----------------------------- Scoring Node --------------------------------------------
        Scoring scoring = new Scoring(hgraph, new String[] {"known", "closeness", "averageDuration"}, 
                new double[] {0.6, 0.3, 0.1});
        scoring.scoreAllNodes();
        long scoreTime = System.currentTimeMillis();
        System.out.println("Scoring All Nodes ... Done! exec time : " + (scoreTime - comTime) + " ms");
        
        // ----------------------------- Coloring Node -------------------------------------------
        Coloring.markColor(hgraph.getNodes(), totalCommunities, Coloring.RANDOM_COLOR, null);
        
        // ------------------- Stores Graph with Calculated in Neo4J (Customer Level) ---------------------
        (new DBAccess()).store(hgraph.getNodes(), hgraph.getEdges(), hgraph.getFullEdges(), tid);

        // ----------------------------- End Customer Section -------------------------------------
        
        // ----------------------------- Comunity View -------------------------------------------
        if (comOfCom) {
            
            // ----------------------------- Building Community Graph -------------------------------
            Graph cGraph = hgraph.buildCommunityGraph(totalCommunities);
            NodeIterable comNodes = cGraph.getNodes();
            List<Edge> comEdges = cGraph.getFullEdges();

            

//community profile attributes
            double[] comArpu = new double[totalCommunities];
            int[] comAis = new int[totalCommunities];
            int[] comCallOtherCarrier = new int[totalCommunities];
            int[] comDaytimeCall = new int[totalCommunities];
            int[] comNighttimeCall = new int[totalCommunities];
            int[] comWeekDayCall = new int[totalCommunities];
            int[] comWeekendCall = new int[totalCommunities];
            int[] comInGroupCall = new int[totalCommunities];
            int[] comOutGroupCall = new int[totalCommunities];
            int[] comDurationCall = new int[totalCommunities];

            for (Node node : hgraph.getNodes()) {
                int communityID = Integer.parseInt(node.getProperty("communityID").toString());
                comArpu[communityID] += node.getProperty("arpu").toString().equals("unknown") ? 0 : Double.parseDouble(node.getProperty("arpu").toString());
                // if (node.getProperty("carrier").toString().equals("AIS")) {
                if (!node.getProperty("arpu").toString().equals("unknown")) {
                    comAis[communityID]++;
                }
            }

            Map<Integer, String> carrierMapper = new HashMap<>();


            for (Edge edge : hgraph.getFullEdges()) {
                int comSource = Integer.parseInt(hgraph.getNodes().get(edge.getSource()).getProperty("communityID").toString());
                int comTarget = Integer.parseInt(hgraph.getNodes().get(edge.getTarget()).getProperty("communityID").toString());
                comDurationCall[comSource] += Integer.parseInt(edge.getProperty("duration").toString());
                if (comSource != comTarget) {
                    comOutGroupCall[comSource]++;
                }

                comInGroupCall[comSource]++;
                //daytime & nighttime profile
                double st = Double.parseDouble(edge.getProperty("startTime").toString());
                if (st >= 5 && st <= 17) {
                    comDaytimeCall[comSource]++;
                } else {
                    comNighttimeCall[comSource]++;
                }

                //weekday & weekend profile
                String cd = edge.getProperty("callDay").toString();
                if (cd.substring(0, 1).equals("S")) {
                    comWeekendCall[comSource]++;
                } else {
                    comWeekDayCall[comSource]++;
                }

                //call to other carrier
                String carrier = edge.getProperty("calleeCarrier").toString();
                if (!(carrier.equals("AIS"))) {
                    comCallOtherCarrier[comSource]++;
                }

            }
//            comNodes = profilingCommunities(comNodes, totalCommunities, comMember, comArpu, comAis, comCallOtherCarrier, comDaytimeCall, comNighttimeCall, comDurationCall, comWeekDayCall, comWeekendCall, comInGroupCall, comOutGroupCall);
            System.out.println("Profiling Community Graph ... Done!");
            
            GraphDistance comDis = new GraphDistance(cGraph);
            comDis.execute(cGraph);
            System.out.println("Calculating Community Graph Distance ... Done!");

            (new DBAccess()).storeCommunity(cGraph.getNodes(), cGraph.getEdges(), cGraph.getFullEdges(), tid);
        }
    }

    private static double[][] findBoundary(int num_community, int[] comMember, double[] comArpu, int[] comAis,
            int[] comCallOtherCarrier, int[] comDaytimeCall, int[] comNighttimeCall, int[] comDurationCall) {
        double[] min = new double[6];
        double[] max = new double[6];

        for (int i = 0; i < 6; i++) {
            min[i] = Integer.MAX_VALUE;
        }

        for (int i = 0; i < num_community; i++) {
            if (comMember[i] < min[0]) {
                min[0] = comMember[i];
            }
            if (comMember[i] > max[0]) {
                max[0] = comMember[i];
            }
            if ((double) comArpu[i] / (double) comAis[i] < min[1]) {
                min[1] = (double) comArpu[i] / (double) comAis[i];
            }
            if ((double) comArpu[i] / (double) comAis[i] > max[1]) {
                max[1] = (double) comArpu[i] / (double) comAis[i];
            }
            if ((double) comAis[i] / (double) comMember[i] < min[2]) {
                min[2] = (double) comAis[i] / (double) comMember[i];
            }
            if ((double) comAis[i] / (double) comMember[i] > max[2]) {
                max[2] = (double) comAis[i] / (double) comMember[i];
            }
            if ((double) comCallOtherCarrier[i] / (double) (comDaytimeCall[i] + comNighttimeCall[i]) < min[3]) {
                min[3] = (double) comCallOtherCarrier[i] / (double) (comDaytimeCall[i] + comNighttimeCall[i]);
            }
            if ((double) comCallOtherCarrier[i] / (double) (comDaytimeCall[i] + comNighttimeCall[i]) > max[3]) {
                max[3] = (double) comCallOtherCarrier[i] / (double) (comDaytimeCall[i] + comNighttimeCall[i]);
            }
            if ((double) comDurationCall[i] / (comDaytimeCall[i] + comNighttimeCall[i]) < min[4]) {
                min[4] = (double) comDurationCall[i] / (comDaytimeCall[i] + comNighttimeCall[i]);
            }
            if ((double) comDurationCall[i] / (comDaytimeCall[i] + comNighttimeCall[i]) > max[4]) {
                max[4] = (double) comDurationCall[i] / (comDaytimeCall[i] + comNighttimeCall[i]);
            }
            if ((double) (comDaytimeCall[i] + comNighttimeCall[i]) / comAis[i] < min[5]) {
                min[5] = (double) (comDaytimeCall[i] + comNighttimeCall[i]) / comAis[i];
            }
            if ((double) (comDaytimeCall[i] + comNighttimeCall[i]) / comAis[i] > max[5]) {
                max[5] = (double) (comDaytimeCall[i] + comNighttimeCall[i]) / comAis[i];
            }
        }

        return new double[][]{min, max};
    }

    private static String mapLevel(double value, double min, double max) {
        double range = max - min;
        if (max == 0 && min == 0) {
            return "None";
        }

        if (value < min + range / 5) {
            return "Very Low";
        } else if (value < min + range * 2 / 5) {
            return "Low";
        } else if (value < min + range * 3 / 5) {
            return "Medium";
        } else if (value < min + range * 4 / 5) {
            return "High";
        } else {
            return "Very High";
        }
    }

    private static Set<Node> profilingCommunities(Set<Node> nodes, int num_community, int[] comMember, double[] comArpu, int[] comAis,
            int[] comCallOtherCarrier, int[] comDaytimeCall, int[] comNighttimeCall, int[] comDurationCall, int[] comWeekdayCall,
            int[] comWeekendCall, int[] comInGroupCall, int[] comOutGroupCall) {

        DecimalFormat df = new DecimalFormat("#.##");
        double[][] maxmin = findBoundary(num_community, comMember, comArpu, comAis, comCallOtherCarrier, comDaytimeCall, comNighttimeCall, comDurationCall);
        double[] min = maxmin[0];
        double[] max = maxmin[1];

        for (Node n : nodes) {
            int communityID = Integer.parseInt(n.getProperty("communityID").toString());
            //community profile attrbutes
            n.setProperty("averageArpu", df.format((double) comArpu[communityID] / (double) comAis[communityID]));
            n.setProperty("aisRatio", df.format((double) comAis[communityID] / Double.parseDouble(n.getProperty("member").toString())));
            n.setProperty("callOtherCarrierRatio", df.format((double) comCallOtherCarrier[communityID] / (double) (comDaytimeCall[communityID] + comNighttimeCall[communityID])));
            n.setProperty("daytimeCall", comDaytimeCall[communityID]);
            n.setProperty("nighttimeCall", comNighttimeCall[communityID]);
            n.setProperty("weekdayCall", comWeekdayCall[communityID]);
            n.setProperty("weekendCall", comWeekendCall[communityID]);
            n.setProperty("inGroupCall", comInGroupCall[communityID]);
            n.setProperty("outGroupCall", comOutGroupCall[communityID]);
            n.setProperty("averageNoOfCall", df.format((double) (comDaytimeCall[communityID] + comNighttimeCall[communityID]) / comAis[communityID]));
            n.setProperty("averageDurationCall", df.format((double) comDurationCall[communityID] / (comDaytimeCall[communityID] + comNighttimeCall[communityID])));

            n.setProperty("memberProfile", mapLevel(comMember[communityID], min[0], max[0]));
            n.setProperty("averageArpuProfile", mapLevel((double) comArpu[communityID] / (double) comAis[communityID], min[1], max[1]));
            n.setProperty("aisRatioProfile", mapLevel((double) comAis[communityID] / (double) comMember[communityID], min[2], max[2]));
            n.setProperty("callOtherCarrierProfile", mapLevel((double) comCallOtherCarrier[communityID] / (double) (comDaytimeCall[communityID] + comNighttimeCall[communityID]), min[3], max[3]));
            n.setProperty("averageDurationProfile", mapLevel((double) comDurationCall[communityID] / (comDaytimeCall[communityID] + comNighttimeCall[communityID]), min[4], max[4]));
            n.setProperty("averageNoOfCallProfile", mapLevel((double) (comDaytimeCall[communityID] + comNighttimeCall[communityID]) / comAis[communityID], min[5], max[5]));
            if ((comDaytimeCall[communityID] - comNighttimeCall[communityID]) < -5) {
                n.setProperty("daytimeNighttimeProfile", "Nighttime");
            } else if ((comDaytimeCall[communityID] - comNighttimeCall[communityID]) > 5) {
                n.setProperty("daytimeNighttimeProfile", "Daytime");
            } else {
                n.setProperty("daytimeNighttimeProfile", "Average");
            }
            if (((double) comWeekdayCall[communityID] / 5 - (double) comWeekendCall[communityID] / 2) < -1) {
                n.setProperty("weekdayWeekendProfile", "Weekend");
            } else if (((double) comWeekdayCall[communityID] / 5 - (double) comWeekendCall[communityID] / 2) > 1) {
                n.setProperty("weekdayWeekendProfile", "Weekday");
            } else {
                n.setProperty("weekdayWeekendProfile", "Average");
            }
            nodes.add(n);
        }

        return nodes;
    }
}
