/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.processingmodule;

import com.seniorproject.graphmodule.Graph;
import com.seniorproject.graphmodule.Node;
import java.util.HashMap;
import java.util.Map;
import java.util.Map.Entry;

/**
 *
 * @author pperfectionist
 */
public class Scoring {
    private Graph hgraph;
    
    private String[] factors;
    
    private Map<String, Double> maxVal;
    private Map<String, Double> minVal;
    private Map<String, Double> weights;
    
    public Scoring(Graph hgraph, String[] factors, double[] weights) {
        this.factors = factors;
        this.weights = new HashMap<>();
        this.maxVal = new HashMap<>();
        this.minVal = new HashMap<>();
        
        int idx = 0;
        for(String factor : factors) {
            this.weights.put(factor, weights[idx++]);
            this.maxVal.put(factor, Double.MIN_VALUE);
            this.minVal.put(factor, Double.MAX_VALUE);
        }
    }
    
    private double toDouble(Object x) {
        return Double.parseDouble(x.toString());
    }
    
    private void findMinMaxEachAttribute() {
        for (Node node : this.hgraph.getNodes()) {
            for(String factor : factors) {
                double tmp = toDouble(node.getProperty(factor).toString());
                if(tmp > this.maxVal.get(factor)) {
                    this.maxVal.put(factor, tmp);
                } else if(tmp < this.minVal.get(factor)) {
                    this.minVal.put(factor, tmp);
                }
            }
        }
    }
    
    private void scoringNode(Node n) {
        double score = 0;
        for(Entry<String, Double> weight : this.weights.entrySet()) {
            score += weight.getValue()*scoringAttribute(toDouble(n.getProperty(weight.getKey())), 
                    this.maxVal.get(weight.getKey()), this.minVal.get(weight.getKey()));
        }
        n.setProperty("score", score);
    }
    
    private double scoringAttribute(double x, double max, double min) {
        if (x == 0 || x <= min) {
            return 0;
        }
        try {
            return Math.log(x - min) / Math.log(max - min);
        } catch (Exception e) {
            return 0;
        }
    }
    
    public  void scoreAllNodes() {
        findMinMaxEachAttribute();
        
        for(Node n : hgraph.getNodes()) {
            this.scoringNode(n);
        }
    }
}
