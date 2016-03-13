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
import java.util.Map.Entry;
import org.neo4j.graphdb.DynamicRelationshipType;
import org.neo4j.graphdb.GraphDatabaseService;
import org.neo4j.graphdb.Node;
import org.neo4j.graphdb.Result;
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

    public static String toTime(String datetime) {
        return datetime.substring(11, 13) + datetime.substring(14, 16) + datetime.substring(17, 19);
    }

    public static void main(String args[]) throws IOException {
        Map<String, Integer> incoming = new HashMap<>();
        Map<String, Integer> outgoing = new HashMap<>();
        Map<String, String> age = new HashMap<>();
        Map<String, String> gender = new HashMap<>();
        Map<String, String> arpu = new HashMap<>();
        Map<String, String> promotion = new HashMap<>();
        try (CSVReader reader = new CSVReader(new FileReader("/Users/KIM/Desktop/sample_cdr.csv"), ',')) {
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

        try (CSVReader reader = new CSVReader(new FileReader("/Users/KIM/Desktop/sample_number.csv"), ',')) {
            String[] nextLine;
            while ((nextLine = reader.readNext()) != null) {
                age.put(nextLine[0],nextLine[1]);
                gender.put(nextLine[0],nextLine[2]);
                arpu.put(nextLine[0],nextLine[3]);
                promotion.put(nextLine[0],nextLine[4]);
            }
            reader.close();
        } 
        
        GraphDatabaseService gdb = new GraphDatabaseFactory().newEmbeddedDatabase("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/database/Neo4j/store.graphdb");

        NodeLabel[] nl = NodeLabel.values();
        int i;
        long time;
        Transaction tx;
        try (CSVReader reader = new CSVReader(new FileReader("/Users/KIM/Desktop/sample_cdr.csv"), ',')) {
        //try (CSVReader reader = new CSVReader(new FileReader("/Users/KIM/Desktop/real_number.csv"), ',')) {
            String[] nextLine;
            i = 0;
            time = System.currentTimeMillis();
            tx = gdb.beginTx();
            Map<String, Node> nodes = new HashMap<>();
            reader.readNext();

            //for number profile
            //gdb.execute( "MATCH (n:Raw) SET n.age = 0, n.gender = 'undefined', n.promotion = '-' RETURN n");

             while ((nextLine = reader.readNext()) != null) {

                //cypher add number profile (slow)
                //gdb.execute( "MATCH (n:Raw{ number: '"+nextLine[0]+"'}) SET n.age = toInt("+nextLine[1]+"), n.gender = '"+nextLine[2]+"', n.promotion = '"+nextLine[3]+"' RETURN n");
                
                //cypher add cdr (slow)
                //gdb.execute("MERGE (p:Raw { number: '"+nextLine[0]+"'}) MERGE (q:Raw { number: '"+nextLine[1]+"'}) CREATE (p)-[r:Call {startDate: toFloat('"+nextLine[2]+"'), startTime: toFloat('"+nextLine[3]+"'), callDay: '"+nextLine[4]+"', duration: toInt('"+nextLine[5]+"')}]->(q) SET q.rnCode = '"+nextLine[6]+"'");
                
                Node a, b;
                if (nodes.containsKey(nextLine[0])) {
                    a = nodes.get(nextLine[0]);
                } else {
                    a = gdb.createNode(nl[0]);
                    a.setProperty("number", nextLine[0]);
                    a.setProperty("incoming", incoming.get(nextLine[0]) == null? 0 : incoming.get(nextLine[0]));
                    a.setProperty("outgoing", outgoing.get(nextLine[0]) == null? 0 : outgoing.get(nextLine[0]));
                    a.setProperty("carrier","AIS");
                    a.setProperty("age", age.get(nextLine[0]) == null? "unknown" : age.get(nextLine[0]));
                    a.setProperty("gender", gender.get(nextLine[0]) == null? "unknown" : gender.get(nextLine[0]));
                    a.setProperty("arpu", arpu.get(nextLine[0]) == null? "unknown" : arpu.get(nextLine[0]));
                    a.setProperty("promotion", promotion.get(nextLine[0]) == null? "unknown" : promotion.get(nextLine[0]));
                    nodes.put(nextLine[0], a);
                }

                if (nodes.containsKey(nextLine[1])) {
                    b = nodes.get(nextLine[1]);
                } else {
                    b = gdb.createNode(nl[0]);
                    b.setProperty("number", nextLine[1]);
                    b.setProperty("incoming", incoming.get(nextLine[1]) == null? 0 : incoming.get(nextLine[1]));
                    b.setProperty("outgoing", outgoing.get(nextLine[1]) == null? 0 : outgoing.get(nextLine[1]));
                    b.setProperty("carrier",nextLine[6]);
                    b.setProperty("age", age.get(nextLine[1]) == null? "unknown" : age.get(nextLine[0]));
                    b.setProperty("gender", gender.get(nextLine[1]) == null? "unknown" : gender.get(nextLine[0]));
                    b.setProperty("arpu", arpu.get(nextLine[0]) == null? "unknown" : arpu.get(nextLine[0]));
                    b.setProperty("promotion", promotion.get(nextLine[1]) == null? "unknown" : promotion.get(nextLine[0]));
                    nodes.put(nextLine[1], b);
                }

                Relationship r = a.createRelationshipTo(b, LINK);
                r.setProperty("startDate", nextLine[2]);
                r.setProperty("startTime", nextLine[3]);
                r.setProperty("callDay", nextLine[4]);
                r.setProperty("duration", nextLine[5]);
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
