package com.seniorproject.processingmodule;

import com.seniorproject.graphmodule.Edge;
import com.seniorproject.graphmodule.Graph;
import com.seniorproject.graphmodule.Node;
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
    
    public static void scoringNode(Node n, double maxCC, double minCC, double maxAvDuration, double minAvDuration, 
            double maxKnown, double minKnown) {
        double score = 0;
        double wCC = 3, wAD = 1, wK = 6;
        score += wCC*scoringAttribute(Double.parseDouble(n.getProperty("closeness").toString()), maxCC, minCC);
        score += wAD*scoringAttribute(Double.parseDouble(n.getProperty("averageDuration").toString()), maxAvDuration, minAvDuration);
        score += wK*scoringAttribute(Double.parseDouble(n.getProperty("known").toString()), maxKnown, minKnown);
        
        n.setProperty("score", score/(wCC+wK));
    }
    
    public static double scoringAttribute(double x, double max, double min) {
        if(x == 0 || x <= min) return 0;
        try {
            return Math.log(x-min)/Math.log(max-min);
        } catch (Exception e) {
            return 0;
        }
    }
    
    public static double toDouble(Object x) {
        return Double.parseDouble(x.toString());
    }
    
    public static String randomColor() {

        int r = (int) (Math.floor((Math.random() * 255 + Math.random() * 255) / 2));
        int g = (int) (Math.floor((Math.random() * 255 + Math.random() * 255) / 2));
        int b = (int) (Math.floor((Math.random() * 255 + Math.random() * 255) / 2));

        String hex = String.format("#%02x%02x%02x", r, g, b);
        return hex;
    }

    public static String[] generateColor(int n) {
        Set<String> colors = new HashSet<>();
        while (colors.size() < n) {
            colors.add(randomColor());
        }
        String[] results = new String[n];
        colors.toArray(results);
        return results;
    }

    public static void markColor(Graph hgraph, int totalCommunities) {
        String[] colors = generateColor(totalCommunities);
        for (Node n : hgraph.getNodes()) {
            n.setProperty("color", colors[Integer.parseInt(n.getProperty("communityID").toString())]);
        }
    }

    public static void main(String[] args) throws IOException {
        long startTime = System.currentTimeMillis();
        Map<String, List<Double>> comparableFilters = new HashMap<>();
        Map<String, List<String>> stringFilters = new HashMap<>();

        String tid = args[0];
        String database = args[1];
        System.out.println("Analyses Database : " + database);
        boolean comOfCom = args[2].equals("1");

        for (int i = 3; i < args.length; i++) {
            String key = args[i++];
            int is_number = Integer.parseInt(args[i++]);
            int args_len = Integer.parseInt(args[i++]);

            if (is_number == 1) {
                List<Double> tmp = new ArrayList<>();
                for (int j = 0; j < args_len; j++, i++) {
                    tmp.add(Double.parseDouble(args[i]));
                }
                comparableFilters.put(key, tmp);
            } else {
                List<String> tmp = new ArrayList<>();
                for (int j = 0; j < args_len; j++, i++) {
                    tmp.add(args[i]);
                }
                stringFilters.put(key, tmp);
            }
            i--;
        }

        Graph hgraph = (new DBAccess()).loadAll(stringFilters, comparableFilters, database);
        long buildGraphTime = System.currentTimeMillis();
        System.out.println("Building Graph ... Done! exec time : " + (buildGraphTime - startTime) + " ms");
        GraphDistance dis = new GraphDistance(hgraph);
        dis.execute(hgraph);
        long calTime = System.currentTimeMillis();
        System.out.println("Calculating Graph Distance ... Done! exec time : " + (calTime - buildGraphTime) + " ms");

        Modularity mod = new Modularity(hgraph);
        int[] com = mod.buildCommunities(hgraph);

        long comTime = System.currentTimeMillis();
        System.out.println("Detecting Communities ... Done! exec time : " + (comTime - calTime) + " ms");
        Set<Integer> tot = new HashSet<>();
        int idx = 0;
        
        double maxCC = 0, minCC = Double.MAX_VALUE, maxAvDuration = 0, minAvDuration = Double.MAX_VALUE, maxKnown = 0, minKnown = Double.MAX_VALUE;
        for (Node node : hgraph.getNodes()) {
            node.setProperty("communityID", com[idx]);
            tot.add(com[idx]);
            idx++;
            
            Double cc = toDouble(node.getProperty("closeness"));
            if(cc > maxCC) {
                maxCC = cc;
            } else if (cc < minCC) {
                minCC = cc;
            }
            
            Double avD = toDouble(node.getProperty("averageDuration"));
            if(avD > maxAvDuration) {
                maxAvDuration = avD;
            } else if (avD < minAvDuration) {
                minAvDuration = avD;
            }
            
            Double kno = toDouble(node.getProperty("known"));
            if(kno > maxKnown) {
                maxKnown = kno;
            } else if (kno < minKnown) {
                minKnown = kno;
            }
        }
        System.out.println("-------------------------------------------");
        System.out.println("Total of Communities : " + tot.size());
        System.out.println("-------------------------------------------");
        
        for(Node n : hgraph.getNodes()) {
            scoringNode(n, maxCC, minCC, maxAvDuration, minAvDuration, maxKnown, minKnown);
        }
        
        markColor(hgraph, tot.size());
        (new DBAccess()).store(hgraph.getNodes(), hgraph.getEdges(),hgraph.getFullEdges(), tid);

        if (comOfCom) {
            Set<Node> comNodes = new HashSet<>();
            List<Edge> comEdges = new ArrayList<>();
            int[] comMember = new int[tot.size()];
            String[] comColor = new String[tot.size()];

            //community profile attributes
            double[] comArpu = new double[tot.size()];
            int[] comAis = new int[tot.size()];
            int[] comCallOtherCarrier = new int[tot.size()];
            int[] comDaytimeCall = new int[tot.size()];
            int[] comNighttimeCall = new int[tot.size()];
            int[] comWeekDayCall = new int[tot.size()];
            int[] comWeekendCall = new int[tot.size()];
            int[] comInGroupCall = new int[tot.size()];
            int[] comOutGroupCall = new int[tot.size()];
            int[] comDurationCall = new int[tot.size()];

            for (Node node : hgraph.getNodes()) {
                int communityID = Integer.parseInt(node.getProperty("communityID").toString());
                comMember[communityID]++;
                comColor[communityID] = node.getProperty("color").toString();
                comArpu[communityID] += node.getProperty("arpu").toString().equals("unknown")? 0 : Double.parseDouble(node.getProperty("arpu").toString());
                // if (node.getProperty("carrier").toString().equals("AIS")) {
                if(!node.getProperty("arpu").toString().equals("unknown")){
                    comAis[communityID]++;
                }
            }
            
            Map<Integer, String>carrierMapper = new HashMap<>();
            
            for (int id = 0; id < tot.size(); id++) {
                Node node = new Node(id);
                node.setProperty("communityID", id);
                node.setProperty("member", comMember[id]);
                node.setProperty("color", comColor[id]);
                comNodes.add(node);
            }

            for (Edge edge : hgraph.getFullEdges()) {
                int comSource = Integer.parseInt(hgraph.getNodes().get(edge.getSource()).getProperty("communityID").toString());
                int comTarget = Integer.parseInt(hgraph.getNodes().get(edge.getTarget()).getProperty("communityID").toString());
                comDurationCall[comSource] += Integer.parseInt(edge.getProperty("duration").toString());
                if (comSource != comTarget) {
                    comEdges.add(new Edge(
                            comSource,
                            comTarget,
                            1.0f,
                            edge.getProperty("startDate").toString(),
                            edge.getProperty("startTime").toString(),
                            edge.getProperty("callDay").toString(),
                            Integer.parseInt(edge.getProperty("duration").toString()),
                            ""
                    ));
                    comOutGroupCall[comSource]++;
                }
                
                comInGroupCall[comSource]++;
                    //daytime & nighttime profile
                    double st = Double.parseDouble(edge.getProperty("startTime").toString());
                    if(st >= 5 && st <= 17) {
                        comDaytimeCall[comSource]++;
                    } else {
                        comNighttimeCall[comSource]++;
                    }

                    //weekday & weekend profile
                    String cd = edge.getProperty("callDay").toString();
                    if(cd.substring(0,1).equals("S")) {
                        comWeekendCall[comSource]++;
                    } else {
                        comWeekDayCall[comSource]++;
                    }

                    //call to other carrier
                    String carrier = edge.getProperty("calleeCarrier").toString();
                    if(!(carrier.equals("AIS"))) {
                        comCallOtherCarrier[comSource]++;
                    }

            }
            comNodes = profilingCommunities(comNodes, tot.size(), comMember, comArpu, comAis, comCallOtherCarrier, comDaytimeCall, comNighttimeCall, comDurationCall, comWeekDayCall, comWeekendCall, comInGroupCall, comOutGroupCall);
            System.out.println("Profiling Community Graph ... Done!");
            Graph comGraph = new Graph(comNodes, comEdges);
            GraphDistance comDis = new GraphDistance(comGraph);
            comDis.execute(comGraph);
            System.out.println("Calculating Community Graph Distance ... Done!");
            
            
            (new DBAccess()).storeCommunity(comGraph.getNodes(), comGraph.getEdges(), comGraph.getFullEdges(), tid);
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
        if(max == 0 && min == 0) {
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
