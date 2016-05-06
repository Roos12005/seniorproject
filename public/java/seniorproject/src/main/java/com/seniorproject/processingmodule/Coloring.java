/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.processingmodule;

import com.seniorproject.graphmodule.Node;
import com.seniorproject.graphmodule.NodeIterable;
import java.util.HashSet;
import java.util.Set;

/**
 *
 * @author pperfectionist
 */
public class Coloring {
    public static final int RANDOM_COLOR = 1;
    
    public Coloring() {
        
    }
    
    private static String randomColor() {

        int r = (int) (Math.floor((Math.random() * 255 + Math.random() * 255) / 2));
        int g = (int) (Math.floor((Math.random() * 255 + Math.random() * 255) / 2));
        int b = (int) (Math.floor((Math.random() * 255 + Math.random() * 255) / 2));

        String hex = String.format("#%02x%02x%02x", r, g, b);
        return hex;
    }

    private static String[] generateColor(int n) {
        Set<String> colors = new HashSet<>();
        while (colors.size() < n) {
            colors.add(randomColor());
        }
        String[] results = new String[n];
        colors.toArray(results);
        return results;
    }

    public static void markColor(NodeIterable nodes, int totalCommunities, int coloringType, String[] colors) {
        if(coloringType == Coloring.RANDOM_COLOR) {
            colors = generateColor(totalCommunities);
            
        }
        
        for (Node n : nodes) {
            n.setProperty("color", colors[Integer.parseInt(n.getProperty("communityID").toString())]);
        }
    }
}
