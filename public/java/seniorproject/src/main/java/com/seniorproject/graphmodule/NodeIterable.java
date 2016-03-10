package com.seniorproject.graphmodule;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;

public class NodeIterable implements Iterable<Node>, Iterator<Node> {
    private Map<Integer, Node> nodes;
    private Map<Node, Integer> invNodes;
    private List<Integer> ids;
    private int count = -1;

    public NodeIterable(){
        this.ids = new ArrayList();
        this.nodes = new HashMap<>();
        this.invNodes = new HashMap<>();
    }

    public boolean add(Node n){
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
    }

    public boolean removeNode(Node node) {
        int id = this.invNodes.get(node);
        this.nodes.remove(id);
        this.ids.remove(node.getID());
        this.invNodes.remove(node);
        return true;
    }

    public int count() {
        return nodes.size();
    }

    public Node get(int source) {
        return this.nodes.get(source);
    }
    
    public boolean contains(int n) {
        return this.nodes.containsKey(n);
    }
    
    public static NodeIterable[] split(NodeIterable nodes, int n) {
        NodeIterable[] aNodes = new NodeIterable[n];
        for(int i=0;i<n;i++) {
            aNodes[i] = new NodeIterable();
        }
        int next = 0;
        for(Node node : nodes) {
               aNodes[next].add(node);
               if(next >= n) {
                   next = next % n;
               }
        }
        
        return aNodes;
    }
}
