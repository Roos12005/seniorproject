package com.seniorproject.graphmodule;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Map.Entry;

public class Node {
    private static final String REGEX_DOUBLE = "[\\x00-\\x20]*[+-]?(((((\\p{Digit}+)(\\.)?((\\p{Digit}+)?)([eE][+-]?(\\p{Digit}+))?)|(\\.((\\p{Digit}+))([eE][+-]?(\\p{Digit}+))?)|(((0[xX](\\p{XDigit}+)(\\.)?)|(0[xX](\\p{XDigit}+)?(\\.)(\\p{XDigit}+)))[pP][+-]?(\\p{Digit}+)))[fFdD]?))[\\x00-\\x20]*";
    private static final String REGEX_INTEGER = "^-?\\d+$";
    
    private int ID;
    private String label;
    
    private Map<String, Object> properties;

    public Node(int s) {
        this.ID = s;
        this.properties = new HashMap<>();
    }

    @Override
    public boolean equals(Object o) {
        return (o instanceof Node) && (((Node) o).getID()) == (this.getID());
    }

    @Override
    public int hashCode() {
        return (getID() + "").hashCode();
    }

    //getter & setter ID

    public int getID() {
        return ID;
    }

    public void setID(int id) {
        this.ID = id;
    }

    public Map<String, Object> getProperties() {
        return this.properties;
    }
    
    public Object getProperty(String name) {
        return this.properties.get(name);
    }
    
    public void setProperty(String name, Object value) {
        this.properties.put(name, value);
    }
    
    public void setProperties(Map<String, Object> props) {
        for(Entry<String, Object> prop : props.entrySet()) {
            this.properties.put(prop.getKey(), prop.getValue());
        }
    }

    public String getLabel() {
        return label;
    }

    public void setLabel(String label) {
        this.label = label;
    }

    public String[] splitProperties() {
        List<String> allProp = new ArrayList();
        
        for(Entry<String, Object> prop : this.properties.entrySet()) {
            allProp.add(prop.getValue().toString());
        }
        
        return allProp.toArray(new String[this.properties.size()]);
    }
    
    public String[] splitPropertiesWithLabel() {
        List<String> allProp = new ArrayList();
        
        allProp.add(this.getLabel());
        for(Entry<String, Object> prop : this.properties.entrySet()) {
            allProp.add(prop.getValue().toString());
        }
        
        return allProp.toArray(new String[this.properties.size()]);
    }
    
    private double toDouble(Object obj) {
        return Double.parseDouble(obj.toString());
    }
}