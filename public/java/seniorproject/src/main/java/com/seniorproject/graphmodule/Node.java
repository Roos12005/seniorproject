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

    //attributes
    private double eccentricity;
    private double closeness;
    private double betweenness;
    private int communityID;
    private String age;
    private String gender;
    private String rnCode;
    private String promotion;
    private int member;
    private int noOfCall;
    private int noOfReceive;

    private String color;

    public Node(int s) {
        this.ID = s;
        this.betweenness = 0;
        this.closeness = 0;
        this.eccentricity = 0;
        this.color = "";
        this.age = "null";
        this.rnCode = "null";
        this.gender = "null";
        this.promotion = "null";
        this.member = 0;
        this.noOfCall = 0;
        this.noOfReceive = 0;
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

    public String getAge() {
        return this.age;
    }

    public void setAge(String age) {
        this.age = age;
    }

    public String getGender() {
        return this.gender;
    }

    public void setGender(String gender) {
        this.gender = gender;
    }

    public String getRnCode() {
        return this.rnCode;
    }

    public void setRnCode(String rnCode) {
        this.rnCode = rnCode;
    }

    public String getPromotion() {
        return this.promotion;
    }

    public void setPromotion(String promotion) {
        this.promotion = promotion;
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

    /**
     * @return the color
     */
    public String getColor() {
        return color;
    }

    /**
     * @param color the color to set
     */
    public void setColor(String color) {
        this.color = color;
    }

    /**
     * @return the color
     */
    public int getMember() {
        return member;
    }

    /**
     * @param color the color to set
     */
    public void setMember(int member) {
        this.member = member;
    }

    public int getNoOfCall() {
        return this.noOfCall;
    }

    public void setNoOfCall(int noOfCall) {
        this.noOfCall = noOfCall;
    }

    public int getNoOfReceive() {
        return this.noOfReceive;
    }

    public void setNoOfReceive(int noOfReceive) {
        this.noOfReceive = noOfReceive;
    }
}
