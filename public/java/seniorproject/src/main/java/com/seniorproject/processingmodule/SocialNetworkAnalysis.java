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
import java.util.HashSet;
import java.util.Random;
import java.util.Set;

/**
 *
 * @author pperfectionist
 */
public class SocialNetworkAnalysis {
    
    public static String randomColor() {
        
        int r = (int) (Math.floor(Math.random()*255));
        int g = (int) (Math.floor(Math.random()*255));;
        int b = (int) (Math.floor(Math.random()*255));;
        
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

        Graph hgraph = (new DBAccess()).loadAll();
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
