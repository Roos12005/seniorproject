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
        
        int r = (int) (Math.floor((Math.random()*255 + Math.random()*255)/2));
        int g = (int) (Math.floor((Math.random()*255 + Math.random()*255)/2));
        int b = (int) (Math.floor((Math.random()*255 + Math.random()*255)/2));
        
        String hex = String.format("#%02x%02x%02x", r, g, b);
        return hex;
    }
    
    public static String[] generateColor(int n) {
        Set<String> colors = new HashSet<>();
        while(colors.size() < n) {
            colors.add(randomColor());
        }
        String[] results = new String[n];
        colors.toArray(results);
        return results;
    }
    
    public static void markColor(Graph hgraph, int totalCommunities) {
        String[] colors = generateColor(totalCommunities);
        for(Node n : hgraph.getNodes()) {
            n.setColor(colors[n.getCommunityID()]);
        }
    }
    
    public static void main(String[] args) throws IOException {
        // temporary read csv for testing performance
        long startTime = System.currentTimeMillis();
//        CSVReader reader = new CSVReader(new FileReader("sampledata.csv"));
//    	String [] nextLine;
//        Set<Node> nodes = new HashSet<Node>();
//        List<Edge> edges = new ArrayList<Edge>();
//    	Node a,b;
////    	reader.readNext();
//        int i = 0;
//        Map<String, Integer> mapperID = new HashMap<>();
//        int countedge = 0;
//        while ((nextLine = reader.readNext()) != null) {
//            countedge++;
//        	if(!mapperID.containsKey(nextLine[0])) mapperID.put(nextLine[0], i);
//                if(!mapperID.containsKey(nextLine[1])) mapperID.put(nextLine[1], i + 500000000);
//                a = new Node(mapperID.get(nextLine[0]));
//        	b = new Node(mapperID.get(nextLine[1]));
//        	nodes.add(a);
//        	nodes.add(b);
//        	
////        	Edge e = new Edge(Integer.parseInt(nextLine[0]),Integer.parseInt(nextLine[1]),Integer.parseInt(nextLine[2]));
//        	Edge e = new Edge(mapperID.get(nextLine[0]),mapperID.get(nextLine[1]),1);
//        	edges.add(e);
//                i++;
//        }
//        
//    	reader.close();
//        Graph hgraph = new Graph(nodes,edges);

        // ------------------------------------------
        Map<String, List<Double>> comparableFilters = new HashMap<>();
        Map<String, List<String>> stringFilters = new HashMap<>();

        boolean comOfCom = args[args.length-1].equals("1")?true:false;
        String tid = args[0];
        for(int i=1; i<args.length - 4; i++) {
            String key = args[i++];
            int is_number = Integer.parseInt(args[i++]);
            int args_len = Integer.parseInt(args[i++]);

            if(is_number == 1) {
                List<Double> tmp = new ArrayList<>();
                for(int j=0; j<args_len; j++, i++) {
                    tmp.add(Double.parseDouble(args[i]));
                }
                comparableFilters.put(key, tmp);
            } else {
                List<String> tmp = new ArrayList<>();
                for(int j=0; j<args_len; j++, i++) {
                    tmp.add(args[i]);
                }
                stringFilters.put(key, tmp);
            }
            i--;
        }

        Graph hgraph = (new DBAccess()).loadAll(stringFilters, comparableFilters);
        long readDataTime = System.currentTimeMillis();
        System.out.println("Reading Data ... Done! exec time : " + (readDataTime-startTime) + " ms");
    	
//        for(Node node : hgraph.getNodes()) {
//            System.out.println(node.getID() + " -> " + node.getAge() + " -> " + node.getGender() + " -> " + node.getRnCode() + " -> " + node.getPromotion());
//        }

        long buildGraphTime = System.currentTimeMillis();
    	System.out.println("Building Graph ... Done! exec time : " + (buildGraphTime-startTime) + " ms");
        GraphDistance dis = new GraphDistance(hgraph);
        dis.execute(hgraph);
        long calTime = System.currentTimeMillis();
        System.out.println("Calculating Graph Distance ... Done! exec time : " + (calTime-buildGraphTime) + " ms");

        
        Modularity mod = new Modularity(hgraph);
    	// Compute Modularity Class
    	int[] com = mod.buildCommunities(hgraph);
    	// TODO : Output com
        
        long comTime = System.currentTimeMillis();
        System.out.println("Detecting Communities ... Done! exec time : " + (comTime-calTime) + " ms");
    	int aa = 0;
    	Set<Integer> tot = new HashSet<>();

        int idx = 0;
        int[] noincom = new int[250];
        for(int kkk=0;kkk<250;kkk++)
               noincom[kkk] = 0;
        for(Node node : hgraph.getNodes()) {
//            System.out.println(node.getID() + " : " + com[idx]);
            node.setCommunityID(com[idx]);
            tot.add(com[idx]);
            noincom[com[idx]]++;
            idx++;
        }
    	System.out.println("-------------------------------------------");
    	System.out.println("Total of Communities : " + tot.size());
    	System.out.println("-------------------------------------------");
        
//        for(int kkk=0;kkk<250;kkk++) {
//            System.out.println("Community #" + kkk + " has " + noincom[kkk] + " members!");
//        }
//    	for(Integer co : tot) {
//    		System.out.println(co);
//    	}
//        System.out.println("-------------------------------------------");
//        System.out.println("Network Diameter");
//        for(Node node : hgraph.getNodes()){
//            System.out.println("Node " + node.getID() + " : BC = " + node.getBetweenness() + " CC = " + node.getCloseness() + " EC = " + node.getEccentricity());
//        }
        
        
        markColor(hgraph, tot.size());
        
        (new DBAccess()).store(hgraph.getNodes(), hgraph.getFullEdges(),tid);

        if(comOfCom){
            Set<Node> comNodes = new HashSet<>();
            List<Edge> comEdges = new ArrayList<>();
            int[] comMember = new int[tot.size()];
            String[] comColor = new String[tot.size()];

            for(Node node : hgraph.getNodes()){
                comMember[node.getCommunityID()]++;
                comColor[node.getCommunityID()] = node.getColor();
            }

            for(int id = 0; id < tot.size(); id++){
                Node node = new Node(id);
                node.setCommunityID(id);
                node.setMember(comMember[id]);
                node.setColor(comColor[id]);
                comNodes.add(node);
            }

            for(Edge edge : hgraph.getEdges()){
                int comSource = hgraph.getNodes().get(edge.getSource()).getCommunityID();
                int comTarget = hgraph.getNodes().get(edge.getTarget()).getCommunityID();
                if(comSource != comTarget){
                    comEdges.add(new Edge(comSource,comTarget,1.0f,edge.getStartDate(),edge.getStartTime(),edge.getCallDay(),edge.getDuration()));  
                }
            }
            
            Graph comGraph = new Graph(comNodes,comEdges);
            GraphDistance comDis = new GraphDistance(comGraph); 
            comDis.execute(comGraph);
            System.out.println("Calculating Community Graph Distance ... Done!");   
//            (new DBAccess()).storeCommunity(comGraph.getNodes(), comGraph.getFullEdges());       
        }

        // (new DBAccess()).store(hgraph.getNodes(), hgraph.getFullEdges(), tid);
//
//        if(comOfCom){
//            Set<Node> comNodes = new HashSet<>();
//            List<Edge> comEdges = new ArrayList<>();
//            int[] comMember = new int[tot.size()];
//            String[] comColor = new String[tot.size()];
//
//            for(Node node : hgraph.getNodes()){
//                comMember[node.getCommunityID()]++;
//                comColor[node.getCommunityID()] = node.getColor();
//            }
//
//            for(int id = 0; id < tot.size(); id++){
//                Node node = new Node(id);
//                node.setCommunityID(id);
//                node.setMember(comMember[id]);
//                node.setColor(comColor[id]);
//                comNodes.add(node);
//            }
//
//            for(Edge edge : hgraph.getEdges()){
//                int comSource = hgraph.getNodes().get(edge.getSource()).getCommunityID();
//                int comTarget = hgraph.getNodes().get(edge.getTarget()).getCommunityID();
//                if(comSource != comTarget){
//                    comEdges.add(new Edge(comSource,comTarget,1.0f,edge.getStartDate(),edge.getStartTime(),edge.getCallDay(),edge.getDuration()));  
//                }
//            }
//            
//            Graph comGraph = new Graph(comNodes,comEdges);
//            GraphDistance comDis = new GraphDistance(comGraph); 
//            comDis.execute(comGraph);
//            System.out.println("Calculating Community Graph Distance ... Done!");   
//            (new DBAccess()).storeCommunity(comGraph.getNodes(), comGraph.getFullEdges(), tid);       
//        }
    }
}
