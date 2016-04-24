/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.mycompany.data.importer;

import com.opencsv.CSVReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStream;
import java.nio.charset.Charset;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;
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
public class Importer {

    public static final DynamicRelationshipType LINK = DynamicRelationshipType.withName("Call");
    public static String TMP_STORAGE_PATH = "";
    public static String SOURCE_DATABASE = "";
    
    public static String toDate(String datetime) {

        return datetime.substring(0, 4) + datetime.substring(5, 7) + datetime.substring(8, 10);
    }

    public static String toTime(String datetime) {
        return datetime.substring(11, 13) + '.' + datetime.substring(14, 16) + datetime.substring(17, 19);
    }
    
    private static void readConfigFile() {
        Properties prop = new Properties();
        InputStream input = null;

        try {
            File jarPath=new File(Importer.class.getProtectionDomain().getCodeSource().getLocation().getPath());
            String propertiesPath=jarPath.getParentFile().getAbsolutePath();
            prop.load(new FileInputStream(propertiesPath+"/config.properties"));

            TMP_STORAGE_PATH = prop.getProperty("tmp_storage_path");
            SOURCE_DATABASE = prop.getProperty("source_database");
        } catch (IOException ex) {
            ex.printStackTrace();
        } finally {
            if (input != null) {
                try {
                    input.close();
                } catch (IOException e) {
                    e.printStackTrace();
                }
            }
        }
    }
    
    public static String toDay(String datetime) {
        Calendar cal = Calendar.getInstance();
        cal.set(Integer.parseInt(datetime.substring(0,4)), Integer.parseInt(datetime.substring(5,7))-1, Integer.parseInt(datetime.substring(8,10)));
        switch(cal.get(Calendar.DAY_OF_WEEK)) {
            case Calendar.SUNDAY:
                return "Sunday";
            case Calendar.MONDAY:
                return "Monday";
            case Calendar.TUESDAY:
                return "Tuesday";
            case Calendar.WEDNESDAY:
                return "Wednesday";
            case Calendar.THURSDAY:
                return "Thursday";
            case Calendar.FRIDAY:
                return "Friday";
            case Calendar.SATURDAY:
                return "Saturday";
            default:
                return "Invalid";
        }
    }

    public static void main(String args[]) throws IOException {
        Map<String, Integer> incoming = new HashMap<>();
        Map<String, Integer> outgoing = new HashMap<>();
        Map<String, Integer> pair_number = new HashMap<>();
        Map<String, Integer> known_number = new HashMap<>();
        Map<String, String> age = new HashMap<>();
        Map<String, String> gender = new HashMap<>();
        Map<String, String> arpu = new HashMap<>();
        Map<String, String> promotion = new HashMap<>();
        readConfigFile();
        try (CSVReader reader = new CSVReader(new FileReader(TMP_STORAGE_PATH + args[0] + "_cdr"), '|')) {
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
                 if (!(pair_number.containsKey(nextLine[0]+","+nextLine[3]))){
                     if(known_number.containsKey(nextLine[0])){
                         known_number.put(nextLine[0], known_number.get(nextLine[0]) + 1);
                     } else {
                         known_number.put(nextLine[0], 1);
                     }
                     pair_number.put(nextLine[0]+","+nextLine[3],1);
                 } 
            }
            
            reader.close();
        }
        try (CSVReader reader = new CSVReader(new FileReader(TMP_STORAGE_PATH + args[0] + "_profile"), ',')) {
            String[] nextLine;
            while ((nextLine = reader.readNext()) != null) {
                age.put(nextLine[0],nextLine[1]);
                gender.put(nextLine[0],nextLine[2]);
                promotion.put(nextLine[0],nextLine[3]);
                arpu.put(nextLine[0],nextLine[4]);
            }
            reader.close();
        } 
        
        GraphDatabaseService gdb = new GraphDatabaseFactory().newEmbeddedDatabase(SOURCE_DATABASE);

        Label nl = DynamicLabel.label(args[0]);
        int i;
        long time;
        Transaction tx;
        try (CSVReader reader = new CSVReader(new FileReader(TMP_STORAGE_PATH + args[0] + "_cdr"), '|')) {
            String[] nextLine;
            i = 0;
            time = System.currentTimeMillis();
            tx = gdb.beginTx();
            Map<String, Node> nodes = new HashMap<>();
            reader.readNext();
            while ((nextLine = reader.readNext()) != null) {
                Node a, b;
                
                if(!incoming.containsKey(nextLine[0])) {
                    continue;
                }
                
                if(!outgoing.containsKey(nextLine[0])) {
                    continue;
                }
                
                if(!known_number.containsKey(nextLine[0])) {
                    continue;
                }
                
                 double average_no_call ;
                    if(known_number.get(nextLine[0]) == 0) {
                        average_no_call = 0;
                    } else {
                        average_no_call = outgoing.get(nextLine[0])/known_number.get(nextLine[0]);
                    }
                 //filter call center
                 if (incoming.get(nextLine[0]) > 14 && outgoing.get(nextLine[0]) == 0 && known_number.get(nextLine[0]) > 14){
                     continue;
                 }
                 //filter salesman
                 // data week 2 : boundary number = 2.60505
                 // data week 3 : boundary number = ???
                 // data week 4 : boundary number = ???
                 else if (incoming.get(nextLine[0]) == 0 && outgoing.get(nextLine[0]) > 14 && known_number.get(nextLine[0]) > 14 && average_no_call < 2.60505){
                     continue;
                 }
                 //filter noisy call
                 else if (incoming.get(nextLine[0]) == 1 && outgoing.get(nextLine[0]) == 0 && Integer.parseInt(nextLine[5]) < 15) {
                     continue;
                 }



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
                if(nextLine[2].equals("Call")) {
                    Relationship r = a.createRelationshipTo(b, LINK);
                    r.setProperty("startDate", Integer.parseInt(toDate(nextLine[1])));
                    r.setProperty("startTime", Double.parseDouble(toTime(nextLine[1])));
                    r.setProperty("callDay", toDay(nextLine[1]));
                    r.setProperty("duration", Integer.parseInt(nextLine[5]));
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
        time = System.currentTimeMillis() - time;
        System.out.println("import of " + i + " relationships took " + time / 1000 + " seconds.");
        gdb.shutdown();
    }
}
