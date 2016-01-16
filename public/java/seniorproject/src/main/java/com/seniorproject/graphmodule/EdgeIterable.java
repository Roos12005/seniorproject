/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.graphmodule;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;

/**
 *
 * @author pperfectionist
 */
public class EdgeIterable implements Iterable<Edge>, Iterator<Edge> {
    private List<Edge> edges;
    private int count = -1;

    public EdgeIterable(){
        this.edges = new ArrayList<>();
    }

    public boolean add(Edge e){
        
        
        return edges.add(e);
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
        return edges.get(count);
    }

    public boolean removeEdge(Edge edge) {
        int idx = edges.indexOf(edge);
        if(idx > -1) {
                edges.remove(idx);
                return true;
        }
        return false;
    }
}
