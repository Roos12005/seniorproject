package com.seniorproject.graphmodule;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

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
    private String carrier;
    private String arpu;
    private String promotion;
    private int noOfOutgoing;
    private int noOfIncoming;
    private String color;
    private int member;


    public Node(int s) {
        this.ID = s;
        this.betweenness = 0;
        this.closeness = 0;
        this.eccentricity = 0;
        this.color = "";
        this.age = "null";
        this.carrier = "null";
        this.gender = "null";
        this.arpu = "null";
        this.promotion = "null";
        this.noOfOutgoing = 0;
        this.noOfIncoming = 0;
        this.member = 0;
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

    //getter & setter ID

    public int getID() {
        return ID;
    }

    public void setID(int id) {
        this.ID = id;
    }

    //getter & setter Eccentricity

    public double getEccentricity() {
        return this.eccentricity;
    }

    public void setEccentricity(double eccentricity) {
        this.eccentricity = eccentricity;
    }

    //getter & setter Closeness Centrality

    public double getCloseness() {
        return this.closeness;
    }

    public void setCloseness(double closeness) {
        this.closeness = closeness;
    }

    //getter & setter Betweenness Centrality

    public double getBetweenness() {
        return this.betweenness;
    }

    public void setBetweenness(double betweenness) {
        this.betweenness = betweenness;
    }

    //getter & setter Age

    public String getAge() {
        return this.age;
    }

    public void setAge(String age) {
        this.age = age;
    }

    //getter & setter Gender

    public String getGender() {
        return this.gender;
    }

    public void setGender(String gender) {
        this.gender = gender;
    }

    //getter & setter carrier

    public String getCarrier() {
        return this.carrier;
    }

    public void setCarrier(String carrier) {
        this.carrier = carrier;
    }

    //getter & setter Promotion

    public String getPromotion() {
        return this.promotion;
    }

    public void setPromotion(String promotion) {
        this.promotion = promotion;
    }

    //getter & setter Label

    public String getLabel() {
        return label;
    }

    public void setLabel(String label) {
        this.label = label;
    }

    //getter & setter CommunityID

    public int getCommunityID() {
        return communityID;
    }

    public void setCommunityID(int communityID) {
        this.communityID = communityID;
    }

    //getter & setter Color

    public String getColor() {
        return color;
    }

    public void setColor(String color) {
        this.color = color;
    }

    //getter & setter No Of. Call

    public int getNoOfOutgoing() {
        return this.noOfOutgoing;
    }

    public void setNoOfOutgoing(int noOfOutgoing) {
        this.noOfOutgoing = noOfOutgoing;
    }

    //getter & setter No. of Receive

    public int getNoOfIncoming() {
        return this.noOfIncoming;
    }

    public void setNoOfIncoming(int noOfIncoming) {
        this.noOfIncoming = noOfIncoming;
    }

    //getter & setter Member

    public int getMember() {
        return member;
    }

    public void setMember(int member) {
        this.member = member;
    }

    //getter & setter ARPU

    public String getArpu() {
        return arpu;
    }

    public void setArpu(String arpu) {
        this.arpu = arpu;
    }
}
