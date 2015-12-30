/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.processingmodule;

import com.seniorproject.graphmodule.Graph;
import com.seniorproject.graphmodule.Node;
import com.seniorproject.storingmodule.DBAccess;
import java.io.IOException;
import java.util.HashSet;
import java.util.Set;

/**
 *
 * @author pperfectionist
 */
public class SocialNetworkAnalysis {
    public static void main(String[] args) throws IOException {
//        Set<Node> nodes;
//        List<Edge> edges;
//        try (CSVReader reader = new CSVReader(new FileReader("testdata.csv"))) {
//            String [] nextLine;
//            nodes = new HashSet<>();
//            edges = new ArrayList<>();
//            Node a,b;
//            while ((nextLine = reader.readNext()) != null) {
//                a = new Node(Integer.parseInt(nextLine[0]));
//                b = new Node(Integer.parseInt(nextLine[1]));
//                nodes.add(a);
//                nodes.add(b);
//                
//                Edge e = new Edge(Integer.parseInt(nextLine[0]),Integer.parseInt(nextLine[1]),1);
//                edges.add(e);
//            }   
//            System.out.println("Reading Data ... Done!");
//        }
//    	Graph hgraph = new Graph(nodes,edges);
        Graph hgraph = (new DBAccess()).loadAll();
    	System.out.println("Building Graph ... Done!");
        GraphDistance dis = new GraphDistance(hgraph);
        dis.execute(hgraph);
        System.out.println("Calculating Graph Distance ... Done!");
        
        Modularity mod = new Modularity(hgraph);
    	// Compute Modularity Class
    	int[] com = mod.buildCommunities(hgraph);
    	// TODO : Output com
    	int aa = 0;
    	Set<Integer> tot = new HashSet<>();

        int idx = 0;
        for(Node node : hgraph.getNodes()) {
            System.out.println(node.getID() + " : " + com[idx]);
            node.setCommunityID(com[idx]);
            tot.add(com[idx]);
            idx++;
        }
    	System.out.println("-------------------------------------------");
    	System.out.println("Total of Communities : " + tot.size());
    	System.out.println("-------------------------------------------");
    	for(Integer co : tot) {
    		System.out.println(co);
    	}
        System.out.println("-------------------------------------------");
        System.out.println("Network Diameter");
        for(Node node : hgraph.getNodes()){
            System.out.println("Node " + node.getID() + " : BC = " + node.getBetweenness() + " CC = " + node.getCloseness() + " EC = " + node.getEccentricity());
        }
        
        (new DBAccess()).store(hgraph.getNodes(), hgraph.getEdges());
    }
}
