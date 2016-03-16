package com.seniorproject.graphmodule;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Map.Entry;

public class Edge {
    int source;
    int target;
    private Map<String, Object> properties;


    public Edge(int s, int t, double w, String sd, String st, String cd, int d, String calleeCarrier) {
        this.source = s;
        this.target = t;
        this.properties = new HashMap<>();
        
        this.properties.put("duration", d);
        this.properties.put("weight", w);
        this.properties.put("startDate", sd);
        this.properties.put("startTime", st);
        this.properties.put("callDay", cd);
        this.properties.put("calleeCarrier", calleeCarrier);
    }

    public Edge(int s, int t, double w) {
        this.source = s;
        this.target = t;
        this.properties = new HashMap<>();
        this.properties.put("weight", w);
    }

    public void increaseWeight(double f) {
        double prevWeight = Double.parseDouble(this.properties.get("weight").toString());
        double newWeight = prevWeight + f;
        this.properties.put("weight", newWeight);
    }

    //getter & setter Source

    public int getSource() {
        return source;
    }

    public void setSource(int source) {
        this.source = source;
    }

    //getter & setter Target

    public int getTarget() {
        return target;
    }

    public void setTarget(int target) {
        this.target = target;
    }

    public Map<String, Object> getProperties() {
        return this.properties;
    }
    
    public Object getProperty(String name) {
        return this.properties.get(name);
    }
    
    public void setProperties(Map<String, Object> props) {
        for(Entry<String, Object> prop : props.entrySet()) {
            this.properties.put(prop.getKey(), prop.getValue());
        }
    }
    
    public void setProperty(String name, Object value) {
        this.properties.put(name, value);
    }
    
    public String[] splitPropertiesWithNode() {
        List<String> allProp = new ArrayList<>();
        
        allProp.add(this.getSource() + "");
        allProp.add(this.getTarget() + "");
        for(Entry<String, Object> prop : this.properties.entrySet()) {
            allProp.add(prop.getValue().toString());
        }
        
        return allProp.toArray(new String[this.properties.size()]);
    }
    
    public String[] getPropertiesName() {
        List<String> allProp = new ArrayList<>();
        
        allProp.add("a_number");
        allProp.add("b_number");
        for(Entry<String, Object> prop : this.properties.entrySet()) {
            allProp.add(prop.getKey());
        }
        
        return allProp.toArray(new String[this.properties.size()]);
    }
}
