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
public class NodeIterable implements Iterable<Node>, Iterator<Node> {
//    private List<Node> nodes;
//    private List<Integer> ids;
    private Map<Integer, Node> nodes;
    private Map<Node, Integer> invNodes;
    private List<Integer> ids;
    private int count = -1;

    public NodeIterable(){
//        this.nodes = new ArrayList<>();
        this.ids = new ArrayList();
        this.nodes = new HashMap<>();
        this.invNodes = new HashMap<>();
    }

    public boolean add(Node n){
//        if(nodes.add(n) && ids.add(n.getID())) {
//            return true;
//        }
//        nodes.remove(n);
//        ids.remove(n.getID());
//        return false;
        this.ids.add(n.getID());
        this.nodes.put(n.getID(), n);
        this.invNodes.put(n, n.getID());
        return true;
    }


    @Override
    public Iterator<Node> iterator() {
        count = -1;
        return this;
    }

    @Override
    public boolean hasNext() {
        return count < (nodes.size() - 1);
    }

    @Override
    public Node next() {
        count++;
        return nodes.get(ids.get(count));
//        return nodes.get(count);
    }


    public boolean removeNode(Node node) {
        int id = this.invNodes.get(node);
        this.nodes.remove(id);
        this.ids.remove(node.getID());
        this.invNodes.remove(node);
        return true;
//        int idx = nodes.indexOf(node);
//        if (idx > -1) {
//                nodes.remove(idx);
//                ids.remove(idx);
//                return true;
//        }
//        return false;
    }


    public int count() {
        return nodes.size();
    }


    public Node get(int source) {
//        for(Node n : nodes) {
//            if(n.getID() == source) {
//                    return n;
//            }
//        }
        return this.nodes.get(source);
//        return null;
    }
    
//    public List<Node> toList() {
//        
//        return nodes;
//    }
    
    public boolean contains(int n) {
        return this.nodes.containsKey(n);
    }
}
