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
    private String rnCode;
    private String promotion;
    private int noOfOutgoing;
    private int noOfIncoming;
    private String color;
    private int member;

    //community profile
    // private int daytimeCall;
    // private int nighttimeCall;
    // private int weekdayCall;
    // private int weekendCall;
    // private int durationCall;
    // private int inGroupCall;
    // private int outGroupCall;
    // private int averageArpu;
    // private double quality;

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
        this.noOfOutgoing = 0;
        this.noOfIncoming = 0;

        //community profile
        this.member = 0;
        // this.daytimeCall = 0;
        // this.nighttimeCall = 0;
        // this.weekdayCall = 0;
        // this.weekendCall = 0;
        // this.durationCall = 0;
        // this.inGroupCall = 0;
        // this.outGroupCall = 0;
        // this.averageArpu = 0;
        // this.conductance = 0;
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

    //getter & setter RnCode

    public String getRnCode() {
        return this.rnCode;
    }

    public void setRnCode(String rnCode) {
        this.rnCode = rnCode;
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


    // Getter & Setter for community profile attributes //

    //getter & setter Member

    public int getMember() {
        return member;
    }

    public void setMember(int member) {
        this.member = member;
    }

    // //getter & setter Daytime Call

    // public int getDaytimeCall() {
    //     return daytimeCall;
    // }

    // public void setDaytimeCall(int daytimeCall) {
    //     this.daytimeCall = daytimeCall;
    // }

    // //getter & setter Nighttime Call

    // public int getNighttimeCall() {
    //     return nighttimeCall;
    // }

    // public void setNighttimeCall(int nighttimeCall) {
    //     this.nighttimeCall = nighttimeCall;
    // }

    // //getter & setter Weekday Call

    // public int getWeekdayCall() {
    //     return weekdayCall;
    // }

    // public void setWeekdayCall(int weekdayCall) {
    //     this.weekdayCall = weekdayCall;
    // }

    // //getter & setter Weekend Call

    // public int getWeekendCall() {
    //     return weekendCall;
    // }

    // public void setWeekendCall(int weekendCall) {
    //     this.weekendCall = weekendCall;
    // }

    // //getter & setter Duration Call

    // public int getDurationCall() {
    //     return durationCall;
    // }

    // public void setDurationCall(int durationCall) {
    //     this.durationCall = durationCall;
    // }

    // //getter & setter In Group Call

    // public int getInGroupCall() {
    //     return inGroupCall;
    // }

    // public void setInGroupCall(int inGroupCall) {
    //     this.inGroupCall = inGroupCall;
    // }

    // //getter & setter Out Group Call

    // public int getOutGroupCall() {
    //     return outGroupCall;
    // }

    // public void setOutGroupCall(int outGroupCall) {
    //     this.outGroupCall = outGroupCall;
    // }

    // //getter & setter Average ARPU

    // public int getAverageArpu() {
    //     return averageArpu;
    // }

    // public void setAverageArpu(int averageArpu) {
    //     this.averageArpu = averageArpu;
    // }

    // //getter & setter Conductance

    // public double getQuality() {
    //     return quality;
    // }

    // public void setQuality(double quality) {
    //     this.quality = quality;
    // }
}
