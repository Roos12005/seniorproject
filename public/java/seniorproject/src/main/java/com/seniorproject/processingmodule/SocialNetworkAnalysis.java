/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.processingmodule;

import com.seniorproject.graphmodule.Graph;
import com.seniorproject.graphmodule.Node;
import com.seniorproject.storingmodule.DBAccess;
import java.awt.Color;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Map.Entry;
import java.util.Random;
import java.util.Set;

/**
 *
 * @author pperfectionist
 */
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
//        if(args[0].equals("0")) {
//            // full graph with args[1] as start date
//        }
        
        Map<String, List<Double>> comparableFilters = new HashMap<>();
        Map<String, List<String>> stringFilters = new HashMap<>();
        
        for(int i=1; i<args.length; i++) {
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
        
//        
//        for(Entry<String, List<Float>> entry : comparableFilters.entrySet()) {
//            String key = entry.getKey();
//            List<Float> value = entry.getValue();
//            System.out.print(key + " - ");
//            for(Float s : value) {
//                System.out.print(s + " ");
//            }
//            System.out.println();
//        }
        
        
        Graph hgraph = (new DBAccess()).loadAll(stringFilters, comparableFilters);
        for(Node node : hgraph.getNodes()) {
            System.out.println(node.getID() + " -> " + node.getAge() + " -> " + node.getGender() + " -> " + node.getRnCode() + " -> " + node.getPromotion());
        }
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
        
        
        markColor(hgraph, tot.size());
        
        
        (new DBAccess()).store(hgraph.getNodes(), hgraph.getEdges());
    }
}
