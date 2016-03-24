/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.mycompany.evolution;

import com.opencsv.CSVReader;
import com.opencsv.CSVWriter;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.util.HashMap;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;
import java.util.Map.Entry;

/**
 *
 * @author pperfectionist
 */
public class Evolution {

    private static String getFilename(String id) {
        return "processed_" + id + "_profile.csv";
    }
    
    private static String[] appendFirst(String s, String[] arr) {
        String[] result = new String[arr.length + 1];
        result[0] = s;
        for(int i=0;i<arr.length;i++) {
            result[i+1] = arr[i];
        }
        return result;
    }

    public static void main(String args[]) throws IOException {
        Map<String, String[][]> evolution = new HashMap<>();
        Map<String, Integer[]> communitySize = new HashMap<>();
        
        for (int i = 0; i < args.length; i++) {
            try (CSVReader reader = new CSVReader(new FileReader(getFilename(args[i])), ',')) {
                String[] nextLine;
                nextLine = reader.readNext();
                int num_idx = 0, com_idx = 0, betw_idx = 0, clo_idx = 0;
                for(int j=0;j<nextLine.length;j++) {
                    switch (nextLine[j]) {
                        case "a_number":
                            num_idx = j;
                            break;
                        case "communityID":
                            com_idx = j;
                            break;
                        case "closeness":
                            clo_idx = j;
                            break;
                        case "betweenness":
                            betw_idx = j;
                            break;
                        default:
                            break;
                    }
                }
                
                while ((nextLine = reader.readNext()) != null) {
                    if(evolution.containsKey(nextLine[num_idx])) {
                        String[][] tmp = evolution.get(nextLine[num_idx]);
                        tmp[i][0] = nextLine[com_idx];
                        tmp[i][1] = nextLine[clo_idx];
                        tmp[i][2] = nextLine[betw_idx];
                        evolution.put(nextLine[num_idx], tmp);
                    } else {
                        String[][] tmp = new String[args.length][3];
                        tmp[i][0] = nextLine[com_idx];
                        tmp[i][1] = nextLine[clo_idx];
                        tmp[i][2] = nextLine[betw_idx];
                        evolution.put(nextLine[num_idx], tmp);
                    }
                    
                    if(communitySize.containsKey(nextLine[com_idx])) {
                        Integer[] new_size = communitySize.get(nextLine[com_idx]);
                        new_size[i] ++;
                        communitySize.put(nextLine[com_idx], new_size);
                    } else {
                        Integer[] new_size = new Integer[args.length];
                        for(int q=0;q<args.length;q++) {
                            new_size[q] = 0;
                        }
                        new_size[i]++;
                        communitySize.put(nextLine[com_idx], new_size);
                    }
                }
            }
        }
        
        CSVWriter writer = new CSVWriter(new FileWriter("evolution_result.csv"), ',');       
        String[] headers = new String[3*args.length + 1];
        headers[0] = "number";
        for(int i=0;i<args.length;i++) {
            headers[3*i+1] = "Week#" + (i+1);
            headers[3*i+2] = "Week#" + (i+1) + "Closeness";
            headers[3*i+3] = "Week#" + (i+1) + "Betweenness";
        }
        writer.writeNext(headers);
        int id=0;
        for(Entry<String, String[][]> m : evolution.entrySet()) {
            List<String> row = new LinkedList<>();
            row.add("" + id++);
            String[][] val = m.getValue();
            for(int q = 0; q< val.length; q++) {
                for(int w = 0; w< val[0].length; w++) {
                    row.add(val[q][w]);
                }
            }
            String[] result = row.toArray(new String[val.length * val[0].length]);
            writer.writeNext(result);
        }
        writer.close();
        
        
        writer = new CSVWriter(new FileWriter("community_stats.csv"), ',');       
        for(Entry<String, Integer[]> e : communitySize.entrySet()) {
            String[] result = new String[e.getValue().length + 1];
            result[0] = e.getKey();
            Integer[] tmp = e.getValue();
            for(int k=0;k<e.getValue().length;k++) {
                result[k+1] = tmp[k] + "";
            }
            writer.writeNext(result);
        }
        writer.close();
    }
    
    
}
