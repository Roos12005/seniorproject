/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.graphmodule;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;

/**
 *
 * @author pperfectionist
 */
public class EdgeIterable implements Iterable<Edge>, Iterator<Edge> {
    private Map<String, Edge> edges;
    private int count = -1;
    private List<String> ids;

    public EdgeIterable(){
        this.edges = new HashMap<>();
        this.ids = new ArrayList<>();
    }

    public boolean add(Edge e){
        if(edges.containsKey(toID(e))) {
            edges.get(toID(e)).increaseWeight(roundDuration(e.getDuration()));
        } else {
            edges.put(toID(e), e);
            ids.add(toID(e));
        }
        return true;
//        return edges.add(e);
    }

    @Override
    public Iterator<Edge> iterator() {
        count = -1;
        return this;
    }

    @Override
    public boolean hasNext() {
        return count < (edges.size() - 1);
    }

    @Override
    public Edge next() {
        count++;
        return edges.get(ids.get(count));
    }

    public boolean removeEdge(Edge edge) {
        this.edges.remove(toID(edge));
        return true;
//        int idx = edges.indexOf(edge);
//        if(idx > -1) {
//                edges.remove(idx);
//                return true;
//        }
//        return false;
    }
    
    public int count() {
        return this.edges.size();
    }
    
    private String toID(Edge e) {
        return e.getSource() + "," + e.getTarget();
    }
    
    private double roundDuration(double d) {
        return 60 * (Math.ceil(d/60));
    }
}
