/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.graphmodule;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 *
 * @author pperfectionist
 */
public class Node {
    private int ID;
    private String label;
    private double eccentricity;
    private double closeness;
    private double betweenness;
    private int communityID;

    public Node(int s) {
        this.ID = s;
        this.betweenness = 0;
        this.closeness = 0;
        this.eccentricity = 0;
//        nei = new ArrayList<>();
//        weights = new HashMap<>();
    }

    public int getID() {
        return ID;
    }

    public void setID(int id) {
        this.ID = id;
    }

    public double getEccentricity() {
        return this.eccentricity;
    }

    public void setEccentricity(double eccentricity) {
        this.eccentricity = eccentricity;
    }

    public double getCloseness() {
        return this.closeness;
    }

    public void setCloseness(double closeness) {
        this.closeness = closeness;
    }

    public double getBetweenness() {
        return this.betweenness;
    }

    public void setBetweenness(double betweenness) {
        this.betweenness = betweenness;
    }

    public void setAttribute(String type, double value) {
        switch (type) {
            case "eccentricity":
                this.setEccentricity(value);
                break;
            case "closenesscentrality":
                this.setCloseness(value);
                break;
            case "betweennesscentrality":
                this.setBetweenness(value);
                break;
            default:
                break;
        }
    }

    @Override
    public boolean equals(Object o) {
        return (o instanceof Node) && (((Node) o).getID()) == (this.getID());
    }

    @Override
    public int hashCode() {
        return (getID() + "").hashCode();
    }

    /**
     * @return the label
     */
    public String getLabel() {
        return label;
    }

    /**
     * @param label the label to set
     */
    public void setLabel(String label) {
        this.label = label;
    }

    /**
     * @return the communityID
     */
    public int getCommunityID() {
        return communityID;
    }

    /**
     * @param communityID the communityID to set
     */
    public void setCommunityID(int communityID) {
        this.communityID = communityID;
    }
}
