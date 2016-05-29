package com.seniorproject.processingmodule;

import com.seniorproject.graphmodule.Graph;
import com.seniorproject.graphmodule.Node;
import com.seniorproject.graphmodule.NodeIterable;
import com.seniorproject.storingmodule.DBAccess;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class SocialNetworkAnalysis {
    
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
        try {
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
            NodeIterable tmpNodes = Coloring.markColor(hgraph.getNodes(), totalCommunities, Coloring.RANDOM_COLOR, null);
            hgraph.setNodes(tmpNodes);

            // ------------------- Stores Graph with Calculated in Neo4J (Customer Level) ---------------------
            (new DBAccess()).store(hgraph.getNodes(), hgraph.getEdges(), hgraph.getFullEdges(), tid);

            // ----------------------------- End Customer Section -------------------------------------

            //----------------------------- Community Section ------------------------------------------
            // ----------------------------- Community View ------------------------------------------
            if (comOfCom) {
                // ----------------------------- Building Community Graph -------------------------------
                Graph cGraph = hgraph.buildCommunityGraph(totalCommunities);

                // ------------------------------- Profiling Communities --------------------------------
                Profiling profiling = new Profiling(cGraph, hgraph, totalCommunities);
                NodeIterable comNodes = profiling.profilingCommunities();
                cGraph.setNodes(comNodes);
                System.out.println("Profiling Community Graph ... Done!");

                // ------------------------- Calculating Centrality for Communities -------------------------
                GraphDistance comDis = new GraphDistance(cGraph);
                comDis.execute(cGraph);
                System.out.println("Calculating Community Graph Distance ... Done!");

                // -------------------------- Stores Communities with Results -----------------------------
                (new DBAccess()).storeCommunity(cGraph.getNodes(), cGraph.getEdges(), cGraph.getFullEdges(), tid);

                // ------------------------------ End Community Section --------------------------------
            }
            System.out.println("Process shut down successfully ... Exit code 0");
        } catch (Exception e) {
            System.out.println("Error occured during processing ... Exit code AA0901");
        }
    }
}
