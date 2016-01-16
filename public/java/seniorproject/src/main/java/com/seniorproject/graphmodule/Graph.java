/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.graphmodule;

import java.util.Collection;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;

/**
 *
 * @author pperfectionist
 */
public class Graph {
    private EdgeIterable edges;
    private NodeIterable nodes;
    
    private Map<Integer, NodeIterable> neighbors;
    
    private Map<Integer, EdgeIterable> outEdges;
    private Map<Integer, EdgeIterable> allEdges;

    public Graph(Set<Node> nodes, List<Edge> edges){
        this.nodes = new NodeIterable();
        this.edges = new EdgeIterable();
        this.neighbors = new HashMap<>();
        this.outEdges = new HashMap<>();
        this.allEdges = new HashMap<>();
        for(Node n : nodes) {
            this.nodes.add(n);
            neighbors.put(n.getID(), new NodeIterable());
            outEdges.put(n.getID(), new EdgeIterable());
            allEdges.put(n.getID(), new EdgeIterable());
        }
//        System.out.println("Adding nodes ... done !");
        for(Edge e : edges) {
            
            this.edges.add(e);
            this.outEdges.get(e.getSource()).add(e);
            this.allEdges.get(e.getSource()).add(e);
            this.allEdges.get(e.getTarget()).add(e);
            if(!neighbors.get(e.source).contains(e.getTarget())) {
                neighbors.get(e.source).add(this.nodes.get(e.getTarget()));
            }
            if(!neighbors.get(e.getTarget()).contains(e.getSource())) {
                neighbors.get(e.getTarget()).add(this.nodes.get(e.getSource()));
            }
        }
        System.out.println("Nodes : " + this.nodes.count() + "\nEdges : " + this.edges.count());
//        neighbors = new HashMap<>();
//        
//        // neighbors
//        for(Node node : this.nodes) {
//            NodeIterable res = new NodeIterable();
//            Set<Node> tmp = new HashSet<>();
//            
//            for (Edge e : edges) {
//                if(e.getSource() == node.getID()) {
//                    tmp.add(this.nodes.get(e.getTarget()));
//                } else if(e.getTarget() == node.getID()) {
//                    tmp.add(this.nodes.get(e.getSource()));
//                }
//            }
//            for(Node m : tmp) {
//                res.add(m);
//            }
//            neighbors.put(node, res);
//        }
        
    }

    public boolean addEdge(Edge edge){
        return edges.add(edge);
    }

    public boolean addNode(Node node){
        return nodes.add(node);
    }

    public boolean addAllEdges(Collection<? extends Edge> clctn){
        EdgeIterable tmp = edges;
        for(Edge e : clctn) {
            if(!edges.add(e)) {
                edges = tmp;
                return false;
            }
        }
        return true;
    }

    public boolean addAllNodes(Collection<? extends Node> clctn){
        NodeIterable tmp = nodes;
        for(Node n : clctn) {
            if(!nodes.add(n)){
                nodes = tmp;
                return false;
            }
        }
        return true;
    }

    public boolean removeEdge(Edge edge){
        return edges.removeEdge(edge);
    }

    public boolean removeNode(Node node){
        return nodes.removeNode(node);
    }

    public boolean removeAllEdges(Collection<? extends Edge> clctn){
        for(Edge e : clctn) {
            edges.removeEdge(e);
        }
        return true;
    }

    public boolean removeAllNodes(Collection<? extends Node> clctn){
        for(Node n : clctn) {
            nodes.removeNode(n);
        }
        return true;
    }

    public NodeIterable getNodes(){
        return nodes;
    }

    public EdgeIterable getEdges(){
        return edges;
    }

    public NodeIterable getNeighbors(Node node){
        return neighbors.get(node.getID());
    }

    public EdgeIterable getEdges(Node node){
//        EdgeIterable res = new EdgeIterable();
//        Set<Edge> tmp = new HashSet<>();
//        for(Edge e : edges) {
//            if(e.getSource() == node.getID() || e.getTarget() == node.getID()) {
//                tmp.add(e);
//            }
//        }
//        
//        for(Edge e : tmp) {
//            res.add(e);
//        }
//        return res;
        return this.allEdges.get(node.getID());
    } 

    public EdgeIterable getOutEdges(Node node){
//        EdgeIterable res = new EdgeIterable();
//        Set<Edge> tmp = new HashSet<>();
//        for(Edge e : edges) {
//            if(e.getSource() == node.getID()) {
//                tmp.add(e);
//            }
//        }
//        for(Edge e : tmp) {
//            res.add(e);
//        }
//        return res;
        return this.outEdges.get(node.getID());
    }

    public Node getOpposite(Node node, Edge edge) {
        if(node.getID() == edge.getSource()){
            return nodes.get(edge.getTarget());
        } else {
            return nodes.get(edge.getSource());
        }
    }

    public Edge getEdge(Node n, Node nei) {
        // TODO : Fill this function
        return null;
    }

    public int getNodeCount(){
        return nodes.count();
    }
}
