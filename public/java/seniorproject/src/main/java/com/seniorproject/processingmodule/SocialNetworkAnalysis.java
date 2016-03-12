package com.seniorproject.processingmodule;

import com.seniorproject.graphmodule.Edge;
import com.seniorproject.graphmodule.Graph;
import com.seniorproject.graphmodule.Node;
import com.seniorproject.storingmodule.DBAccess;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;

public class SocialNetworkAnalysis {

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
        for (Node node : hgraph.getNodes()) {
            node.setProperty("communityID", com[idx]);
            tot.add(com[idx]);
            idx++;
        }
        System.out.println("-------------------------------------------");
        System.out.println("Total of Communities : " + tot.size());
        System.out.println("-------------------------------------------");

        markColor(hgraph, tot.size());
        (new DBAccess()).store(hgraph.getNodes(), hgraph.getFullEdges(), tid);

//        if (comOfCom) {
//            Set<Node> comNodes = new HashSet<>();
//            List<Edge> comEdges = new ArrayList<>();
//            int[] comMember = new int[tot.size()];
//            String[] comColor = new String[tot.size()];
//
//            for (Node node : hgraph.getNodes()) {
//                comMember[node.getCommunityID()]++;
//                comColor[node.getCommunityID()] = node.getColor();
//            }
//
//            for (int id = 0; id < tot.size(); id++) {
//                Node node = new Node(id);
//                node.setCommunityID(id);
//                node.setMember(comMember[id]);
//                node.setColor(comColor[id]);
//                comNodes.add(node);
//            }
//
//            for (Edge edge : hgraph.getEdges()) {
//                int comSource = hgraph.getNodes().get(edge.getSource()).getCommunityID();
//                int comTarget = hgraph.getNodes().get(edge.getTarget()).getCommunityID();
//                if (comSource != comTarget) {
//                    comEdges.add(new Edge(comSource, comTarget, 1.0f, edge.getStartDate(), edge.getStartTime(), edge.getCallDay(), edge.getDuration()));
//                }
//            }
//
//            Graph comGraph = new Graph(comNodes, comEdges);
//            GraphDistance comDis = new GraphDistance(comGraph);
//            comDis.execute(comGraph);
//            System.out.println("Calculating Community Graph Distance ... Done!");
//            (new DBAccess()).storeCommunity(comGraph.getNodes(), comGraph.getFullEdges(), tid);       
//        }
    }
}
