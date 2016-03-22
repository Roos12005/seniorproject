/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.mycompany.data.migrator;

import com.opencsv.CSVReader;
import java.io.File;
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
public class Migrator {

    public static final DynamicRelationshipType LINK = DynamicRelationshipType.withName("Call");

    public static String toDate(String datetime) {

        return datetime.substring(0, 4) + datetime.substring(5, 7) + datetime.substring(8, 10);
    }

    public static String toTome(String datetime) {
        return datetime.substring(11, 13) + datetime.substring(14, 16) + datetime.substring(17, 19);
    }

    private static Map<String, Node> createNodes(GraphDatabaseService gdb, String file, String label) throws IOException {
        Label nl = DynamicLabel.label(label);
        Transaction tx;
        Map<String, Node> nodes = new HashMap<>();
        try (CSVReader reader = new CSVReader(new FileReader("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/storage/tmp_migrate/" + file), ',')) {
            String[] nextLine;
            int i = 0;
            tx = gdb.beginTx();
            
            nextLine = reader.readNext();
            String[] columns = new String[nextLine.length];
            for(int idx=0;idx<nextLine.length;idx++) {
                columns[idx] = nextLine[idx];
            }
            
            while ((nextLine = reader.readNext()) != null) {
                Node a;
                a = gdb.createNode(nl);
                for(int idx=0;idx<nextLine.length;idx++) {
                    a.setProperty(columns[idx], nextLine[idx]);
                }
                nodes.put(nextLine[0], a);

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

        return nodes;
    }
    
    private static void createRelationships(GraphDatabaseService gdb, String file, Map<String, Node> nodes) throws IOException {
        Transaction tx;
        try (CSVReader reader = new CSVReader(new FileReader("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/storage/tmp_migrate/" + file), ',')) {
            String[] nextLine;
            int i = 0;
            tx = gdb.beginTx();
            
            nextLine = reader.readNext();
            String[] columns = new String[nextLine.length];
            for(int idx=0;idx<nextLine.length;idx++) {
                columns[idx] = nextLine[idx];
            }
            
            while ((nextLine = reader.readNext()) != null) {
                Node a = nodes.get(nextLine[0]);
                Node b = nodes.get(nextLine[1]);
                
                Relationship r = a.createRelationshipTo(b, LINK);
                for(int idx=0;idx<nextLine.length;idx++) {
                    r.setProperty(columns[idx], nextLine[idx]);
                }

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
    }

    public static void main(String args[]) throws IOException {

        GraphDatabaseService gdb = new GraphDatabaseFactory().newEmbeddedDatabase("/Users/pperfectionist/Documents/Neo4j/default.graphdb");
        
        // TODO : this should loop through all files
        Map<String, Node> storedNodes = createNodes(gdb, "processed_"+args[0]+"_profile.csv", "Processed" + args[1]);
        createRelationships(gdb, "processed_"+args[0]+"_cdr.csv", storedNodes);
        Map<String, Node> storedComNodes = createNodes(gdb, "processed_com_"+args[0]+"_profile.csv", "ProcessedCom" + args[1]);
        createRelationships(gdb, "processed_com_"+args[0]+"_cdr.csv", storedComNodes);
        
        gdb.shutdown();
        
//        File folder = new File("/Applications/XAMPP/xamppfiles/htdocs/seniorproject/storage/tmp_migrate/");
//        File[] listOfFiles = folder.listFiles();
//
//        for (int i = 0; i < listOfFiles.length; i++) {
//          if (listOfFiles[i].isFile()) {
//            System.out.println("File " + listOfFiles[i].getName());
//          } else if (listOfFiles[i].isDirectory()) {
//            System.out.println("Directory " + listOfFiles[i].getName());
//          }
//        }
    }
}
