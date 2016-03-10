/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.storingmodule;

import com.seniorproject.graphmodule.Node;
import com.seniorproject.graphmodule.NodeIterable;
import org.neo4j.graphdb.DynamicLabel;
import org.neo4j.graphdb.GraphDatabaseService;
import org.neo4j.graphdb.Label;
import org.neo4j.graphdb.Transaction;
import org.neo4j.rest.graphdb.RestGraphDatabase;

/**
 *
 * @author pperfectionist
 */
public class StoringAgent implements Runnable {
    private Thread t;
    private String threadName;
    private NodeIterable nodes;
    private GraphDatabaseService gdb;
    
    public StoringAgent(GraphDatabaseService gdb, NodeIterable nodes, String name) {
        this.gdb = gdb;
        this.nodes = nodes;
        this.threadName = name;
    }
    
    @Override
    public void run() {
        System.out.println(threadName + " started");
         
        Label nl = DynamicLabel.label("Test");
        Transaction tx;
        tx = gdb.beginTx();
        org.neo4j.graphdb.Node a, b;
        int i=0;
        for(Node n : nodes) {    
            a = gdb.createNode(nl);
            a.setProperty("number", n.getLabel());
             if (i % 50000 == 0) {
                    tx.success();
                    tx.finish();
                    tx = gdb.beginTx();
            }
            i++;
        }
         tx.success();
        tx.finish();
        gdb.shutdown();
    }
    
    public void start() {
        t = new Thread (this, threadName);
        t.start ();
    }
    
}
