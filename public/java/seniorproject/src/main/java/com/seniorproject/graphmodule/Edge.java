package com.seniorproject.graphmodule;

public class Edge {
    int source;
    int target;
    double weight;
    int duration;
    String startDate;
    String startTime;
    String callDay;


    public Edge(int s, int t, double w, String sd, String st, String cd, int d) {
        source = s;
        target = t;
        duration = d;
        startDate = sd;
        startTime = st;
        callDay = cd;
        weight = w;
    }

    public Edge(int s, int t, double w) {
        source = s;
        target = t;
        weight = w;
    }

    public void increaseWeight(double f) {
        this.weight += f;
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

    //getter & setter Weight

    public double getWeight() {
        return weight;
    }

    public void setWeight(double weight) {
        this.weight = weight;
    }

    //getter & setter Duration

    public int getDuration() {
        return duration;
    }

    public void setDuration(int duration) {
        this.duration = duration;
    }

    //getter & setter StartDate

    public String getStartDate() {
        return startDate;
    }

    public void setStartDate(String startDate) {
        this.startDate = startDate;
    }

    //getter & setter StartTime

    public String getStartTime() {
        return startTime;
    }

    public void setStartTime(String startTime) {
        this.startTime = startTime;
    }

    //getter & setter CallDay
    
    public String getCallDay() {
        return callDay;
    }

    public void setCallDay(String callDay) {
        this.callDay = callDay;
    }
}
