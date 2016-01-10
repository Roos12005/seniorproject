/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.graphmodule;

/**
 *
 * @author pperfectionist
 */
public class Edge {
    int source;
    int target;
    float weight;
    int duration;
    String startDate;
    String startTime;
    String callDay;


    public Edge(int s, int t, float w, String sd, String st, String cd, int d) {
        source = s;
        target = t;
        duration = d;
        startDate = sd;
        startTime = st;
        callDay = cd;
        weight = w;
    }

    public Edge(int s, int t, float w) {
        source = s;
        target = t;
        weight = w;
    }

    public int getSource() {
        return source;
    }

    public void setSource(int source) {
        this.source = source;
    }

    public int getTarget() {
        return target;
    }

    public void setTarget(int target) {
        this.target = target;
    }

    public float getWeight() {
        return weight;
    }

    public void setWeight(float weight) {
        this.weight = weight;
    }

    public int getDuration() {
        return duration;
    }

    public void setDuration(int duration) {
        this.duration = duration;
    }

    public String getStartDate() {
        return startDate;
    }

    public void setStartDate(String startDate) {
        this.startDate = startDate;
    }

    public String getStartTime() {
        return startTime;
    }

    public void setStartTime(String startTime) {
        this.startTime = startTime;
    }
    
    public String getCallDay() {
        return callDay;
    }

    public void setCallDay(String callDay) {
        this.callDay = callDay;
    }
}
