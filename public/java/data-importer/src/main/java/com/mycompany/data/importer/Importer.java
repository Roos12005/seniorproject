/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.mycompany.data.importer;

import com.opencsv.CSVReader;
import java.io.FileReader;
import java.io.IOException;
import java.util.HashMap;
import java.util.Map;
import org.neo4j.graphdb.DynamicRelationshipType;
import org.neo4j.graphdb.DynamicLabel;
import org.neo4j.graphdb.GraphDatabaseService;
import org.neo4j.graphdb.Label;
import org.neo4j.graphdb.Node;
import org.neo4j.graphdb.Relationship;
import org.neo4j.graphdb.Transaction;
import org.neo4j.graphdb.factory.GraphDatabaseFactory;

/**
 *
 * @author pperfectionist
 */
public class Importer {

    public static final DynamicRelationshipType LINK = DynamicRelationshipType.withName("Call");

    public static String toDate(String datetime) {

        return datetime.substring(0, 4) + datetime.substring(5, 7) + datetime.substring(8, 10);
    }

    public static String toTome(String datetime) {
        return datetime.substring(11, 13) + datetime.substring(14, 16) + datetime.substring(17, 19);
    }

    public static void main(String args[]) throws IOException {
        Map<String, Integer> incoming = new HashMap<>();
        Map<String, Integer> outgoing = new HashMap<>();
        try (CSVReader reader = new CSVReader(new FileReader("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/storage/tmp_db_store/" + args[0] + "_cdr"), ',')) {
            String[] nextLine;
            
            while ((nextLine = reader.readNext()) != null) {
                if (incoming.containsKey(nextLine[1])) {
                    incoming.put(nextLine[1], incoming.get(nextLine[1]) + 1);
                } else {
                    incoming.put(nextLine[1], 1);
                }
                
                if (outgoing.containsKey(nextLine[0])) {
                    outgoing.put(nextLine[0], outgoing.get(nextLine[0]) + 1);
                } else {
                    outgoing.put(nextLine[0], 1);
                }
            }
            
            reader.close();
        } 
        
        GraphDatabaseService gdb = new GraphDatabaseFactory().newEmbeddedDatabase("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/database/Neo4j/store.graphdb");

        Label nl = DynamicLabel.label(args[0]);
        int i;
        long time;
        Transaction tx;
        try (CSVReader reader = new CSVReader(new FileReader("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/storage/tmp_db_store/" + args[0] + "_cdr"), ',')) {
            String[] nextLine;
            i = 0;
            time = System.currentTimeMillis();
            tx = gdb.beginTx();
            Map<String, Node> nodes = new HashMap<>();
            reader.readNext();
            while ((nextLine = reader.readNext()) != null) {
                Node a, b;
                if (nodes.containsKey(nextLine[0])) {
                    a = nodes.get(nextLine[0]);
                } else {
                   
                    a = gdb.createNode(nl);
                    a.setProperty("number", nextLine[0]);
                    a.setProperty("incoming", incoming.get(nextLine[0]) == null? 0 : incoming.get(nextLine[0]));
                    a.setProperty("outgoing", outgoing.get(nextLine[0]) == null? 0 : outgoing.get(nextLine[0]));
                    nodes.put(nextLine[0], a);
                }

                if (nodes.containsKey(nextLine[1])) {
                    b = nodes.get(nextLine[1]);
                } else {
                    b = gdb.createNode(nl);
                    b.setProperty("number", nextLine[1]);
                    b.setProperty("incoming", incoming.get(nextLine[1]) == null? 0 : incoming.get(nextLine[1]));
                    b.setProperty("outgoing", outgoing.get(nextLine[1]) == null? 0 : outgoing.get(nextLine[1]));
                    nodes.put(nextLine[1], b);
                }

                Relationship r = a.createRelationshipTo(b, LINK);
//                r.setProperty("startTime", nextLine[1]);
//                r.setProperty("startDate", nextLine[1]);
//                r.setProperty("duration", nextLine[5]);
//                r.setProperty("callDay", "");
                r.setProperty("startTime", "");
                r.setProperty("startDate", "");
                r.setProperty("duration", "");
                r.setProperty("callDay", "");
                if (i % 50000 == 0) {
                    tx.success();
                    tx.finish();
                    tx = gdb.beginTx();
                }
                i++;
            }
        }

        tx.success();
        tx.finish();
        time = System.currentTimeMillis() - time;
        System.out.println("import of " + i + " relationships took " + time / 1000 + " seconds.");
        gdb.shutdown();
    }
}
