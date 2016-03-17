/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.mycompany.data.importer;

import com.opencsv.CSVReader;
import java.io.FileReader;
import java.io.IOException;
import java.util.Calendar;
import java.util.Date;
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

    public static String toTime(String datetime) {
        return datetime.substring(11, 13) + datetime.substring(14, 16) + datetime.substring(17, 19);
    }
    
    public static String toDay(String datetime) {
        Calendar cal = Calendar.getInstance();
        cal.set(Integer.parseInt(datetime.substring(0,4)), Integer.parseInt(datetime.substring(5,7)), Integer.parseInt(datetime.substring(8,10)));
        switch(cal.get(Calendar.DAY_OF_WEEK)) {
            case 0:
                return "Sunday";
            case 1:
                return "Monday";
            case 2:
                return "Tuesday";
            case 3:
                return "Wednesday";
            case 4:
                return "Thursday";
            case 5:
                return "Friday";
            case 6:
                return "Saturday";
            default:
                return "Invalid";
        }
    }

    public static void main(String args[]) throws IOException {
        Map<String, Integer> incoming = new HashMap<>();
        Map<String, Integer> outgoing = new HashMap<>();
        Map<String, String> age = new HashMap<>();
        Map<String, String> gender = new HashMap<>();
        Map<String, String> arpu = new HashMap<>();
        Map<String, String> promotion = new HashMap<>();
        
        try (CSVReader reader = new CSVReader(new FileReader("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/storage/tmp_db_store/" + args[0] + "_cdr"), '|')) {
            String[] nextLine;
            
            while ((nextLine = reader.readNext()) != null) {
                if (incoming.containsKey(nextLine[3])) {
                    incoming.put(nextLine[3], incoming.get(nextLine[3]) + 1);
                } else {
                    incoming.put(nextLine[3], 1);
                }
                
                if (outgoing.containsKey(nextLine[0])) {
                    outgoing.put(nextLine[0], outgoing.get(nextLine[0]) + 1);
                } else {
                    outgoing.put(nextLine[0], 1);
                }
            }
            
            reader.close();
        }
        
        try (CSVReader reader = new CSVReader(new FileReader("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/storage/tmp_db_store/" + args[0] + "_profile"), ',')) {
            String[] nextLine;
            while ((nextLine = reader.readNext()) != null) {
                age.put(nextLine[0],nextLine[1]);
                gender.put(nextLine[0],nextLine[2]);
                promotion.put(nextLine[0],nextLine[3]);
                arpu.put(nextLine[0],nextLine[4]);
            }
            reader.close();
        } 
        
        GraphDatabaseService gdb = new GraphDatabaseFactory().newEmbeddedDatabase("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/database/Neo4j/store.graphdb");

        Label nl = DynamicLabel.label(args[0]);
        int i;
        long time;
        Transaction tx;
        try (CSVReader reader = new CSVReader(new FileReader("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/storage/tmp_db_store/" + args[0] + "_cdr"), '|')) {
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
                    a.setProperty("carrier","AIS");
                    a.setProperty("age", age.get(nextLine[0]) == null? "unknown" : age.get(nextLine[0]));
                    a.setProperty("gender", gender.get(nextLine[0]) == null? "unknown" : gender.get(nextLine[0]));
                    a.setProperty("arpu", arpu.get(nextLine[0]) == null? "unknown" : arpu.get(nextLine[0]));
                    a.setProperty("promotion", promotion.get(nextLine[0]) == null? "unknown" : promotion.get(nextLine[0]));
                    nodes.put(nextLine[0], a);
                }

                if (nodes.containsKey(nextLine[3])) {
                    b = nodes.get(nextLine[3]);
                } else {
                    b = gdb.createNode(nl);
                    b.setProperty("number", nextLine[3]);
                    b.setProperty("incoming", incoming.get(nextLine[3]) == null? 0 : incoming.get(nextLine[3]));
                    b.setProperty("outgoing", outgoing.get(nextLine[3]) == null? 0 : outgoing.get(nextLine[3]));
                    b.setProperty("carrier",nextLine[4]);
                    b.setProperty("age", age.get(nextLine[3]) == null? "unknown" : age.get(nextLine[3]));
                    b.setProperty("gender", gender.get(nextLine[3]) == null? "unknown" : gender.get(nextLine[3]));
                    b.setProperty("arpu", arpu.get(nextLine[3]) == null? "unknown" : arpu.get(nextLine[3]));
                    b.setProperty("promotion", promotion.get(nextLine[3]) == null? "unknown" : promotion.get(nextLine[3]));
                    nodes.put(nextLine[3], b);
                }

                Relationship r = a.createRelationshipTo(b, LINK);
                r.setProperty("startDate", Integer.parseInt(toDate(nextLine[1])));
                r.setProperty("startTime", Double.parseDouble(toTime(nextLine[1])));
                r.setProperty("callDay", toDay(nextLine[1]));
                r.setProperty("duration", Integer.parseInt(nextLine[5]));
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
