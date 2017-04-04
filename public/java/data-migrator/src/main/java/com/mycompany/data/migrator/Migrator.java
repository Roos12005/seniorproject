/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.mycompany.data.migrator;

import com.opencsv.CSVReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Properties;
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
    
    public static String BASE_DIR;
    public static String TMP_MIGRATE_PATH;
    public static String TARGET_DATABASE;

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
        try (CSVReader reader = new CSVReader(new FileReader(TMP_MIGRATE_PATH + file), ',')) {
            String[] nextLine;
            int i = 0;
            tx = gdb.beginTx();
            
            nextLine = reader.readNext();
            String[] columns = new String[nextLine.length];
            for(int idx=0;idx<nextLine.length;idx++) {
                System.out.println(nextLine[idx]);
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
    
    private static void createRelationships(GraphDatabaseService gdb, String file, Map<String, Node> nodes, String label) throws IOException {
        DynamicRelationshipType lab = DynamicRelationshipType.withName(label);
        Transaction tx;
        
        
        try (CSVReader reader = new CSVReader(new FileReader(TMP_MIGRATE_PATH + file), ',')) {
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
                
                Relationship r = a.createRelationshipTo(b, lab);
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
    
    public static void readConfig() throws IOException {
        Properties prop = new Properties();
        InputStream input = null;
        File jarPath=new File(Migrator.class.getProtectionDomain().getCodeSource().getLocation().getPath());
        String propertiesPath=jarPath.getAbsolutePath();
        propertiesPath = propertiesPath.substring(0, propertiesPath.indexOf("java\\") + 5) + "configuration/";
        prop.load(new FileInputStream(propertiesPath+"config.properties"));
        BASE_DIR = prop.getProperty("base_dir");
        TMP_MIGRATE_PATH = BASE_DIR + prop.getProperty("tmp_migrate_path");
        TARGET_DATABASE = BASE_DIR + prop.getProperty("target_database");
    }

    public static void main(String args[]) throws IOException {
        readConfig();
        GraphDatabaseService gdb = new GraphDatabaseFactory().newEmbeddedDatabase(TARGET_DATABASE);
        
        File folder = new File(TMP_MIGRATE_PATH);
        File[] listOfFiles = folder.listFiles();
        List<String> done = new ArrayList<>();
        for (int i = 1; i < listOfFiles.length; i++) {
            if (listOfFiles[i].isFile()) {
                String n = listOfFiles[i].getName().replaceAll("\\D+","");
                if(done.contains(n)) {
                    continue;
                }
                Long time = System.currentTimeMillis() ;
                System.out.println();System.out.println(">>>");
                System.out.println(n);
                Map<String, Node> storedNodes = createNodes(gdb, "processed_"+n+"_profile.csv", "Processed" + n);
                createRelationships(gdb, "processed_"+n+"_full_cdr.csv", storedNodes, "Call");
                createRelationships(gdb, "processed_"+n+"_aggregated_cdr.csv", storedNodes, "aCall");
                Map<String, Node> storedComNodes = createNodes(gdb, "processed_com_"+n+"_profile.csv", "ProcessedCom" + n);
                createRelationships(gdb, "processed_com_"+n+"_full_cdr.csv", storedComNodes, "Call");
                createRelationships(gdb, "processed_com_"+n+"_aggregated_cdr.csv", storedComNodes, "aCall");
                done.add(n);
                time = System.currentTimeMillis() - time;
                System.out.println("Migrating ID: "  + n + " took " + time / 1000 + " seconds.");
            }
        }
        
        gdb.shutdown();
        
        File file = new File(TMP_MIGRATE_PATH);      
        String[] myFiles;    
           if(file.isDirectory()){
               myFiles = file.list();
               for (int i=0; i<myFiles.length; i++) {
                   File myFile = new File(file, myFiles[i]); 
                   myFile.delete();
               }
        }
    }
}
