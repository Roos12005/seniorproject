/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.mycompany.data.importer;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;

import com.opencsv.CSVReader;
import com.opencsv.CSVWriter;
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
import java.text.DecimalFormat;
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
    public static String BASE_DIR = "";
    public static String TMP_STORAGE_PATH = "";
    public static String SOURCE_DATABASE = "";
    public static String OUTPUTFILE = "features_";
    public static String OUTPUTFILE_PAIR = "features_";
    
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
            String propertiesPath=jarPath.getAbsolutePath();
            propertiesPath = propertiesPath.substring(0, propertiesPath.indexOf("java/") + 5) + "configuration/";
            prop.load(new FileInputStream(propertiesPath+"config.properties"));
            BASE_DIR = prop.getProperty("base_dir");
            TMP_STORAGE_PATH = BASE_DIR + prop.getProperty("tmp_storage_path");
            SOURCE_DATABASE = BASE_DIR + prop.getProperty("source_database");
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

        //feature extraction number
        Map<String, Integer> calling_number = new HashMap<>();
        Map<String, Double> totalDuration = new HashMap<>();
        Map<String, Integer> known_number = new HashMap<>();
        Map<String, Integer> count_daytime_weekday = new HashMap<>();
        Map<String, Integer> count_nighttime_weekday = new HashMap<>();
        Map<String, Integer> count_daytime_weekend = new HashMap<>();
        Map<String, Integer> count_nighttime_weekend = new HashMap<>();
        Map<String, Double> duration_daytime_weekday = new HashMap<>();
        Map<String, Double> duration_nighttime_weekday = new HashMap<>();
        Map<String, Double> duration_daytime_weekend = new HashMap<>();
        Map<String, Double> duration_nighttime_weekend = new HashMap<>();
        //-------------------------------------------------------
        //feature extraction number pair
        Map<String, String> pair_number = new HashMap<>();
        Map<String, Integer> count_pair_call = new HashMap<>();
        Map<String, Double> duration_pair = new HashMap<>();
        Map<String, Integer> pair_count_day_day = new HashMap<>();
        Map<String, Integer> pair_count_night_day = new HashMap<>();
        Map<String, Integer> pair_count_day_end = new HashMap<>();
        Map<String, Integer> pair_count_night_end = new HashMap<>();
        Map<String, Double> pair_dura_day_day = new HashMap<>();
        Map<String, Double> pair_dura_night_day = new HashMap<>();
        Map<String, Double> pair_dura_day_end = new HashMap<>();
        Map<String, Double> pair_dura_night_end = new HashMap<>();
        //-------------------------------------------------------
        Map<String, Integer> incoming = new HashMap<>();
        Map<String, Integer> outgoing = new HashMap<>();
        Map<String, String> age = new HashMap<>();
        Map<String, String> gender = new HashMap<>();
        Map<String, String> arpu = new HashMap<>();
        Map<String, String> promotion = new HashMap<>();
        readConfigFile();
        try (CSVReader reader = new CSVReader(new FileReader(TMP_STORAGE_PATH + args[0] + "_cdr"), '|')) {
            String[] nextLine;
            reader.readNext();
            while ((nextLine = reader.readNext()) != null) {
                calling_number.put(nextLine[0], 1);

                //check start time call
                if(Double.parseDouble(toTime(nextLine[1])) <= 5.0 || Double.parseDouble(toTime(nextLine[1])) >= 17.00){
                    if(toDay(nextLine[1]).substring(0,1).equals("S")){
                        //number
                        if (count_nighttime_weekend.containsKey(nextLine[0])) {
                            count_nighttime_weekend.put(nextLine[0], count_nighttime_weekend.get(nextLine[0]) + 1);
                            duration_nighttime_weekend.put(nextLine[0], duration_nighttime_weekend.get(nextLine[0]) + Double.parseDouble(nextLine[5]));
                        } else {
                            count_nighttime_weekend.put(nextLine[0], 1);
                            duration_nighttime_weekend.put(nextLine[0], Double.parseDouble(nextLine[5]));
                        }
                        //pair number
                        if (pair_count_night_end.containsKey(nextLine[0]+","+nextLine[3])) {
                            pair_count_night_end.put(nextLine[0]+","+nextLine[3], pair_count_night_end.get(nextLine[0]+","+nextLine[3]) + 1);
                            pair_dura_night_end.put(nextLine[0]+","+nextLine[3], pair_dura_night_end.get(nextLine[0]+","+nextLine[3]) + Double.parseDouble(nextLine[5]));
                        } else {
                            pair_count_night_end.put(nextLine[0]+","+nextLine[3], 1);
                            pair_dura_night_end.put(nextLine[0]+","+nextLine[3], Double.parseDouble(nextLine[5]));
                        }
                    } else {
                        //number
                        if (count_nighttime_weekday.containsKey(nextLine[0])) {
                            count_nighttime_weekday.put(nextLine[0], count_nighttime_weekday.get(nextLine[0]) + 1);
                            duration_nighttime_weekday.put(nextLine[0], duration_nighttime_weekday.get(nextLine[0]) + Double.parseDouble(nextLine[5]));
                        } else {
                            count_nighttime_weekday.put(nextLine[0], 1);
                            duration_nighttime_weekday.put(nextLine[0], Double.parseDouble(nextLine[5]));
                        }
                        //pair number
                        if (pair_count_night_day.containsKey(nextLine[0]+","+nextLine[3])) {
                            pair_count_night_day.put(nextLine[0]+","+nextLine[3], pair_count_night_day.get(nextLine[0]+","+nextLine[3]) + 1);
                            pair_dura_night_day.put(nextLine[0]+","+nextLine[3], pair_dura_night_day.get(nextLine[0]+","+nextLine[3]) + Double.parseDouble(nextLine[5]));
                        } else {
                            pair_count_night_day.put(nextLine[0]+","+nextLine[3], 1);
                            pair_dura_night_day.put(nextLine[0]+","+nextLine[3], Double.parseDouble(nextLine[5]));
                        }
                    }
                } else {
                    if(toDay(nextLine[1]).substring(0,1).equals("S")){
                        //number
                        if (count_daytime_weekend.containsKey(nextLine[0])) {
                            count_daytime_weekend.put(nextLine[0], count_daytime_weekend.get(nextLine[0]) + 1);
                            duration_daytime_weekend.put(nextLine[0], duration_daytime_weekend.get(nextLine[0]) + Double.parseDouble(nextLine[5]));
                        } else {
                            count_daytime_weekend.put(nextLine[0], 1);
                            duration_daytime_weekend.put(nextLine[0], Double.parseDouble(nextLine[5]));
                        }
                        //pair number
                        if (pair_count_day_end.containsKey(nextLine[0]+","+nextLine[3])) {
                            pair_count_day_end.put(nextLine[0]+","+nextLine[3], pair_count_day_end.get(nextLine[0]+","+nextLine[3]) + 1);
                            pair_dura_day_end.put(nextLine[0]+","+nextLine[3], pair_dura_day_end.get(nextLine[0]+","+nextLine[3]) + Double.parseDouble(nextLine[5]));
                        } else {
                            pair_count_day_end.put(nextLine[0]+","+nextLine[3], 1);
                            pair_dura_day_end.put(nextLine[0]+","+nextLine[3], Double.parseDouble(nextLine[5]));
                        }
                    } else {
                        //number
                        if (count_daytime_weekday.containsKey(nextLine[0])) {
                            count_daytime_weekday.put(nextLine[0], count_daytime_weekday.get(nextLine[0]) + 1);
                            duration_daytime_weekday.put(nextLine[0], duration_daytime_weekday.get(nextLine[0]) + Double.parseDouble(nextLine[5]));
                        } else {
                            count_daytime_weekday.put(nextLine[0], 1);
                            duration_daytime_weekday.put(nextLine[0], Double.parseDouble(nextLine[5]));
                        }
                        //pair number
                        if (pair_count_day_day.containsKey(nextLine[0]+","+nextLine[3])) {
                            pair_count_day_day.put(nextLine[0]+","+nextLine[3], pair_count_day_day.get(nextLine[0]+","+nextLine[3]) + 1);
                            pair_dura_day_day.put(nextLine[0]+","+nextLine[3], pair_dura_day_day.get(nextLine[0]+","+nextLine[3]) + Double.parseDouble(nextLine[5]));
                        } else {
                            pair_count_day_day.put(nextLine[0]+","+nextLine[3], 1);
                            pair_dura_day_day.put(nextLine[0]+","+nextLine[3], Double.parseDouble(nextLine[5]));
                        }
                    }
                }
                //total duration
                if (totalDuration.containsKey(nextLine[0])) {
                    totalDuration.put(nextLine[0], totalDuration.get(nextLine[0]) + Double.parseDouble(nextLine[5]));
                } else {
                    totalDuration.put(nextLine[0], Double.parseDouble(nextLine[5]));
                }
                //count incoming
                if (incoming.containsKey(nextLine[3])) {
                    incoming.put(nextLine[3], incoming.get(nextLine[3]) + 1);
                } else {
                    incoming.put(nextLine[3], 1);
                }
                //count outgoing
                if (outgoing.containsKey(nextLine[0])) {
                    outgoing.put(nextLine[0], outgoing.get(nextLine[0]) + 1);
                } else {
                    outgoing.put(nextLine[0], 1);
                }
                //count known number
                if (!(pair_number.containsKey(nextLine[0]+","+nextLine[3]))){
                     if(known_number.containsKey(nextLine[0])){
                         known_number.put(nextLine[0], known_number.get(nextLine[0]) + 1);
                     } else {
                         known_number.put(nextLine[0], 1);
                     }
                     pair_number.put(nextLine[0]+","+nextLine[3],nextLine[4]);
                } 
                //count a and b call
                if (count_pair_call.containsKey(nextLine[0]+","+nextLine[3])) {
                    count_pair_call.put(nextLine[0]+","+nextLine[3],count_pair_call.get(nextLine[0]+","+nextLine[3]) + 1);
                } else {
                    count_pair_call.put(nextLine[0]+","+nextLine[3],1);
                }
                //count a and b duration
                if (duration_pair.containsKey(nextLine[0]+","+nextLine[3])) {
                    duration_pair.put(nextLine[0]+","+nextLine[3],duration_pair.get(nextLine[0]+","+nextLine[3]) + Double.parseDouble(nextLine[5]));
                } else {
                    duration_pair.put(nextLine[0]+","+nextLine[3], Double.parseDouble(nextLine[5]));
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
        //Feature Extraction ----------------------------------------------------------------------
        if(args[1].equals("1")){
            System.out.println("run");
            DecimalFormat df = new DecimalFormat("#.###");
            //Features extraction - number
            try (CSVWriter csvOutput = new CSVWriter(new FileWriter(OUTPUTFILE + args[0] + ".csv"),',')) {
                
                //write header file
                String[] header = {"number","no of calling","no of incoming","no of receiver","avg dura","count dt wd call","avg dura dt wd call","count dt we call","avg dura dt we call","count nt wd call","avg dura nt wd call","count nt we call","avg dura nt we call"};
                csvOutput.writeNext(header);

                //add number with features
                for (Map.Entry<String, Integer> entry : calling_number.entrySet()){
                    String[] row = new String[13];
                    row[0] = entry.getKey();
                    row[1] = Integer.toString(outgoing.get(entry.getKey()));
                    row[2] = Integer.toString(incoming.get(entry.getKey()) == null? 0 : incoming.get(entry.getKey()));
                    row[3] = Integer.toString(known_number.get(entry.getKey()) == null? 0 : known_number.get(entry.getKey()));
                    row[4] = df.format(totalDuration.get(entry.getKey()) / (double)outgoing.get(entry.getKey()));
                    row[5] = Integer.toString(count_daytime_weekday.get(entry.getKey()) == null? 0 : count_daytime_weekday.get(entry.getKey()));
                    row[6] = count_daytime_weekday.get(entry.getKey()) == null? "0" :  df.format(duration_daytime_weekday.get(entry.getKey()) / (double)count_daytime_weekday.get(entry.getKey()));
                    row[7] = Integer.toString(count_daytime_weekend.get(entry.getKey()) == null? 0 : count_daytime_weekend.get(entry.getKey()));
                    row[8] = count_daytime_weekend.get(entry.getKey()) == null? "0" : df.format(duration_daytime_weekend.get(entry.getKey()) / (double)count_daytime_weekend.get(entry.getKey()));
                    row[9] = Integer.toString(count_nighttime_weekday.get(entry.getKey()) == null? 0 : count_nighttime_weekday.get(entry.getKey()));
                    row[10] = count_nighttime_weekday.get(entry.getKey()) == null? "0" : df.format(duration_nighttime_weekday.get(entry.getKey()) / (double)count_nighttime_weekday.get(entry.getKey()));
                    row[11] = Integer.toString(count_nighttime_weekend.get(entry.getKey()) == null? 0 : count_nighttime_weekend.get(entry.getKey()));
                    row[12] = count_nighttime_weekend.get(entry.getKey()) == null? "0" : df.format(duration_nighttime_weekend.get(entry.getKey()) / (double)count_nighttime_weekend.get(entry.getKey()));
                    csvOutput.writeNext(row);
                }

                csvOutput.close();
            } catch (IOException e) {
                e.printStackTrace();
            }

            //Features extraction - number pair
            try (CSVWriter csvOutput = new CSVWriter(new FileWriter(OUTPUTFILE_PAIR + args[0] + "_pair.csv"),',')) {
                
                //write header file
                String[] header = {"a number","b number","b carrier","a calling","b calling","a avg dura","b avg dura","count dt wd call","avg dura dt wd call","count dt we call","avg dura dt we call","count nt wd call","avg dura nt wd call","count nt we call","avg dura nt we call"};
                csvOutput.writeNext(header);

                //add number with features
                for (Map.Entry<String, String> entry : pair_number.entrySet()){
                    String[] row = new String[15];
                    String anum = entry.getKey().split(",")[0];
                    String bnum = entry.getKey().split(",").length == 1? "" : entry.getKey().split(",")[1];
                    row[0] = anum;
                    row[1] = bnum;
                    row[2] = entry.getValue();
                    row[3] = Integer.toString(count_pair_call.get(entry.getKey()) == null? 0 : count_pair_call.get(entry.getKey()));
                    row[4] = Integer.toString(count_pair_call.get(bnum + "," + anum) == null? 0 : count_pair_call.get(bnum + "," + anum));
                    row[5] = df.format(duration_pair.get(entry.getKey()) / (double)count_pair_call.get(entry.getKey()));
                    row[6] = duration_pair.get(bnum + "," + anum) == null? "0" : df.format(duration_pair.get(bnum + "," + anum) / (double)count_pair_call.get(bnum + "," + anum));
                    int count_all = (pair_count_day_day.get(entry.getKey()) == null? 0 : pair_count_day_day.get(entry.getKey())) + (pair_count_day_day.get(bnum + "," + anum) == null? 0 : pair_count_day_day.get(bnum + "," + anum));
                    double dura_all = (pair_dura_day_day.get(entry.getKey()) == null? 0 : pair_dura_day_day.get(entry.getKey())) + (pair_dura_day_day.get(bnum + "," + anum) == null? 0 : pair_dura_day_day.get(bnum + "," + anum));
                    row[7] = Integer.toString(count_all);
                    row[8] = count_all == 0? "0" : df.format(dura_all/count_all);
                    count_all = (pair_count_day_end.get(entry.getKey()) == null? 0 : pair_count_day_end.get(entry.getKey())) + (pair_count_day_end.get(bnum + "," + anum) == null? 0 : pair_count_day_end.get(bnum + "," + anum));
                    dura_all = (pair_dura_day_end.get(entry.getKey()) == null? 0 : pair_dura_day_end.get(entry.getKey())) + (pair_dura_day_end.get(bnum + "," + anum) == null? 0 : pair_dura_day_end.get(bnum + "," + anum));
                    row[9] = Integer.toString(count_all);
                    row[10] = count_all == 0? "0" : df.format(dura_all/count_all);
                    count_all = (pair_count_night_day.get(entry.getKey()) == null? 0 : pair_count_night_day.get(entry.getKey())) + (pair_count_night_day.get(bnum + "," + anum) == null? 0 : pair_count_night_day.get(bnum + "," + anum));
                    dura_all = (pair_dura_night_day.get(entry.getKey()) == null? 0 : pair_dura_night_day.get(entry.getKey())) + (pair_dura_night_day.get(bnum + "," + anum) == null? 0 : pair_dura_night_day.get(bnum + "," + anum));
                    row[11] = Integer.toString(count_all);
                    row[12] = count_all == 0? "0" : df.format(dura_all/count_all);
                    count_all = (pair_count_night_end.get(entry.getKey()) == null? 0 : pair_count_night_end.get(entry.getKey())) + (pair_count_night_end.get(bnum + "," + anum) == null? 0 : pair_count_night_end.get(bnum + "," + anum));
                    dura_all = (pair_dura_night_end.get(entry.getKey()) == null? 0 : pair_dura_night_end.get(entry.getKey())) + (pair_dura_night_end.get(bnum + "," + anum) == null? 0 : pair_dura_night_end.get(bnum + "," + anum));
                    row[13] = Integer.toString(count_all);
                    row[14] = count_all == 0? "0" : df.format(dura_all/count_all);
                    csvOutput.writeNext(row);
                }

                csvOutput.close();
            } catch (IOException e) {
                e.printStackTrace();
            }
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
