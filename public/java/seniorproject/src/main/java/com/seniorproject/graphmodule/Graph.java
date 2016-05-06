package com.seniorproject.graphmodule;

import java.util.ArrayList;
import java.util.Collection;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;


public class Graph {
    private EdgeIterable edges;
    private NodeIterable nodes;
    private List<Edge> fullEdges;
    private Map<Integer, NodeIterable> neighbors;
    private Map<Integer, EdgeIterable> outEdges;
    private Map<Integer, EdgeIterable> allEdges;

    public Graph(Set<Node> nodes, List<Edge> edges){
        this.nodes = new NodeIterable();
        this.edges = new EdgeIterable();
        this.fullEdges = new ArrayList<>();
        this.neighbors = new HashMap<>();
        this.outEdges = new HashMap<>();
        this.allEdges = new HashMap<>();
        
        for(Node n : nodes) {
            this.nodes.add(n);
            neighbors.put(n.getID(), new NodeIterable());
            outEdges.put(n.getID(), new EdgeIterable());
            allEdges.put(n.getID(), new EdgeIterable());
        }
        for(Edge e : edges) {
            this.fullEdges.add(new Edge(e));
            this.edges.add(new Edge(e));
            this.outEdges.get(e.getSource()).add(e);
            this.allEdges.get(e.getSource()).add(e);
            this.allEdges.get(e.getTarget()).add(e);
            if(!neighbors.get(e.getSource()).contains(e.getTarget())) {
                neighbors.get(e.getSource()).add(this.nodes.get(e.getTarget()));
            }
            if(!neighbors.get(e.getTarget()).contains(e.getSource())) {
                neighbors.get(e.getTarget()).add(this.nodes.get(e.getSource()));
            }
        }
        System.out.println("Nodes : " + this.nodes.count() + "\nEdges : " + this.edges.count());
       
        
        
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
        return this.allEdges.get(node.getID());
    } 

    public EdgeIterable getOutEdges(Node node){
        return this.outEdges.get(node.getID());
    }

    public Node getOpposite(Node node, Edge edge) {
        if(node.getID() == edge.getSource()){
            return nodes.get(edge.getTarget());
        } else {
            return nodes.get(edge.getSource());
        }
    }

    public int getNodeCount(){
        return nodes.count();
    }
    
    public List<Edge> getFullEdges() {
        return this.fullEdges;
    }
    
    private int[] measureCommunitiesSize(int totalCommunities, String[] communitiesColor) {
        int[] communities = new int[totalCommunities];
        
        for(Node n : this.getNodes()) {
            int comID = Integer.parseInt(n.getProperty("communityID").toString());
            communities[comID]++;
            communitiesColor[comID] = n.getProperty("color").toString();
        }
        
        return communities;
    }
    
    public Graph buildCommunityGraph(int totalCommunities) {
        Set<Node> comNodes = new HashSet<>();
        List<Edge> comEdges = new ArrayList<>();
        String[] communitiesColor = new String[totalCommunities];
        int[] communitiesSize = measureCommunitiesSize(totalCommunities, communitiesColor);
        
        for(int idx=0; idx<totalCommunities; idx++) {
            Node tmp = new Node(idx);
            tmp.setProperty("communityID", idx);
            tmp.setProperty("member", communitiesSize[idx]);
            tmp.setProperty("color", communitiesColor[idx]);
            comNodes.add(tmp);
        }
        
        for(Edge edge : this.getFullEdges()) {
            int comSource = Integer.parseInt(this.getNodes().get(edge.getSource()).getProperty("communityID").toString());
            int comTarget = Integer.parseInt(this.getNodes().get(edge.getTarget()).getProperty("communityID").toString());
            if(comSource != comTarget) {
                comEdges.add(new Edge(
                            comSource,
                            comTarget,
                            1.0f,
                            edge.getProperty("startDate").toString(),
                            edge.getProperty("startTime").toString(),
                            edge.getProperty("callDay").toString(),
                            Integer.parseInt(edge.getProperty("duration").toString()),
                            ""
                ));
            }
        }
        
        return new Graph(comNodes, comEdges);
    }
   
}
