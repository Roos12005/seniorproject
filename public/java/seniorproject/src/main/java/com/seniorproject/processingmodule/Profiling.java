/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.processingmodule;

import com.seniorproject.graphmodule.Edge;
import com.seniorproject.graphmodule.Graph;
import com.seniorproject.graphmodule.Node;
import com.seniorproject.graphmodule.NodeIterable;
import java.text.DecimalFormat;
import java.util.HashMap;
import java.util.Map;

/**
 *
 * @author pperfectionist
 */
public class Profiling {

    private Graph hgraph;
    private int totalCommunities;
    
    private double[] comArpu;
    private int[] comAis;
    private int[] comCallOtherCarrier;
    private int[] comDaytimeCall;
    private int[] comNighttimeCall;
    private int[] comWeekDayCall;
    private int[] comWeekendCall;
    private int[] comInGroupCall;
    private int[] comOutGroupCall;
    private int[] comDurationCall;
    private int[] comMember;

    public Profiling(Graph hgraph, int totalCommunities) {
        this.hgraph = hgraph;
        this.totalCommunities = totalCommunities;
        this.comArpu = new double[totalCommunities];
        this.comAis = new int[totalCommunities];
        this.comCallOtherCarrier = new int[totalCommunities];
        this.comDaytimeCall = new int[totalCommunities];
        this.comNighttimeCall = new int[totalCommunities];
        this.comWeekDayCall = new int[totalCommunities];
        this.comWeekendCall = new int[totalCommunities];
        this.comInGroupCall = new int[totalCommunities];
        this.comOutGroupCall = new int[totalCommunities];
        this.comDurationCall = new int[totalCommunities];
        this.comMember = new int[totalCommunities];
    }
    
    

    private void initParams() {
        for (Node node : this.hgraph.getNodes()) {
            int communityID = Integer.parseInt(node.getProperty("communityID").toString());
            comArpu[communityID] += node.getProperty("arpu").toString().equals("unknown") ? 0 : Double.parseDouble(node.getProperty("arpu").toString());
            if (!node.getProperty("arpu").toString().equals("unknown")) {
                comAis[communityID]++;
            }
        }

        Map<Integer, String> carrierMapper = new HashMap<>();

        for (Edge edge : this.hgraph.getFullEdges()) {
            int comSource = Integer.parseInt(hgraph.getNodes().get(edge.getSource()).getProperty("communityID").toString());
            int comTarget = Integer.parseInt(hgraph.getNodes().get(edge.getTarget()).getProperty("communityID").toString());
            this.comDurationCall[comSource] += Integer.parseInt(edge.getProperty("duration").toString());
            if (comSource != comTarget) {
                this.comOutGroupCall[comSource]++;
            }

            this.comInGroupCall[comSource]++;
            //daytime & nighttime profile
            double st = Double.parseDouble(edge.getProperty("startTime").toString());
            if (st >= 5 && st <= 17) {
                this.comDaytimeCall[comSource]++;
            } else {
                this.comNighttimeCall[comSource]++;
            }

            //weekday & weekend profile
            String cd = edge.getProperty("callDay").toString();
            if (cd.substring(0, 1).equals("S")) {
                this.comWeekendCall[comSource]++;
            } else {
                this.comWeekDayCall[comSource]++;
            }

            //call to other carrier
            String carrier = edge.getProperty("calleeCarrier").toString();
            if (!(carrier.equals("AIS"))) {
                this.comCallOtherCarrier[comSource]++;
            }

        }
    }
    
    private String mapLevel(double value, double min, double max) {
        double range = max - min;
        if (max == 0 && min == 0) {
            return "None";
        }

        if (value < min + range / 5) {
            return "Very Low";
        } else if (value < min + range * 2 / 5) {
            return "Low";
        } else if (value < min + range * 3 / 5) {
            return "Medium";
        } else if (value < min + range * 4 / 5) {
            return "High";
        } else {
            return "Very High";
        }
    }
    
    private double[][] findBoundary() {
        double[] min = new double[6];
        double[] max = new double[6];

        for (int i = 0; i < 6; i++) {
            min[i] = Integer.MAX_VALUE;
        }

        for (int i = 0; i < this.totalCommunities; i++) {
            if (this.comMember[i] < min[0]) {
                min[0] = this.comMember[i];
            }
            if (this.comMember[i] > max[0]) {
                max[0] = this.comMember[i];
            }
            if ((double) this.comArpu[i] / (double) this.comAis[i] < min[1]) {
                min[1] = (double) this.comArpu[i] / (double) this.comAis[i];
            }
            if ((double) this.comArpu[i] / (double) this.comAis[i] > max[1]) {
                max[1] = (double) this.comArpu[i] / (double) this.comAis[i];
            }
            if ((double) this.comAis[i] / (double) this.comMember[i] < min[2]) {
                min[2] = (double) this.comAis[i] / (double) this.comMember[i];
            }
            if ((double) this.comAis[i] / (double) this.comMember[i] > max[2]) {
                max[2] = (double) this.comAis[i] / (double) this.comMember[i];
            }
            if ((double) this.comCallOtherCarrier[i] / (double) (this.comDaytimeCall[i] + this.comNighttimeCall[i]) < min[3]) {
                min[3] = (double) this.comCallOtherCarrier[i] / (double) (this.comDaytimeCall[i] + this.comNighttimeCall[i]);
            }
            if ((double) this.comCallOtherCarrier[i] / (double) (this.comDaytimeCall[i] + this.comNighttimeCall[i]) > max[3]) {
                max[3] = (double) this.comCallOtherCarrier[i] / (double) (this.comDaytimeCall[i] + this.comNighttimeCall[i]);
            }
            if ((double) this.comDurationCall[i] / (this.comDaytimeCall[i] + this.comNighttimeCall[i]) < min[4]) {
                min[4] = (double) this.comDurationCall[i] / (this.comDaytimeCall[i] + this.comNighttimeCall[i]);
            }
            if ((double) this.comDurationCall[i] / (this.comDaytimeCall[i] + this.comNighttimeCall[i]) > max[4]) {
                max[4] = (double) this.comDurationCall[i] / (this.comDaytimeCall[i] + this.comNighttimeCall[i]);
            }
            if ((double) (this.comDaytimeCall[i] + this.comNighttimeCall[i]) / this.comAis[i] < min[5]) {
                min[5] = (double) (this.comDaytimeCall[i] + this.comNighttimeCall[i]) / this.comAis[i];
            }
            if ((double) (this.comDaytimeCall[i] + this.comNighttimeCall[i]) / this.comAis[i] > max[5]) {
                max[5] = (double) (this.comDaytimeCall[i] + this.comNighttimeCall[i]) / this.comAis[i];
            }
        }
        return new double[][]{min, max};
    }

     public NodeIterable profilingCommunities() {
         NodeIterable nodes = hgraph.getNodes();
        DecimalFormat df = new DecimalFormat("#.##");
        double[][] maxmin = findBoundary();
        double[] min = maxmin[0];
        double[] max = maxmin[1];

        for (Node n : nodes) {
            int communityID = Integer.parseInt(n.getProperty("communityID").toString());
            //community profile attrbutes
            n.setProperty("averageArpu", df.format((double) comArpu[communityID] / (double) comAis[communityID]));
            n.setProperty("aisRatio", df.format((double) comAis[communityID] / Double.parseDouble(n.getProperty("member").toString())));
            n.setProperty("callOtherCarrierRatio", df.format((double) comCallOtherCarrier[communityID] / (double) (comDaytimeCall[communityID] + comNighttimeCall[communityID])));
            n.setProperty("daytimeCall", comDaytimeCall[communityID]);
            n.setProperty("nighttimeCall", comNighttimeCall[communityID]);
            n.setProperty("weekdayCall", comWeekDayCall[communityID]);
            n.setProperty("weekendCall", comWeekendCall[communityID]);
            n.setProperty("inGroupCall", comInGroupCall[communityID]);
            n.setProperty("outGroupCall", comOutGroupCall[communityID]);
            n.setProperty("averageNoOfCall", df.format((double) (comDaytimeCall[communityID] + comNighttimeCall[communityID]) / comAis[communityID]));
            n.setProperty("averageDurationCall", df.format((double) comDurationCall[communityID] / (comDaytimeCall[communityID] + comNighttimeCall[communityID])));

            n.setProperty("memberProfile", mapLevel(comMember[communityID], min[0], max[0]));
            n.setProperty("averageArpuProfile", mapLevel((double) comArpu[communityID] / (double) comAis[communityID], min[1], max[1]));
            n.setProperty("aisRatioProfile", mapLevel((double) comAis[communityID] / (double) comMember[communityID], min[2], max[2]));
            n.setProperty("callOtherCarrierProfile", mapLevel((double) comCallOtherCarrier[communityID] / (double) (comDaytimeCall[communityID] + comNighttimeCall[communityID]), min[3], max[3]));
            n.setProperty("averageDurationProfile", mapLevel((double) comDurationCall[communityID] / (comDaytimeCall[communityID] + comNighttimeCall[communityID]), min[4], max[4]));
            n.setProperty("averageNoOfCallProfile", mapLevel((double) (comDaytimeCall[communityID] + comNighttimeCall[communityID]) / comAis[communityID], min[5], max[5]));
            if ((comDaytimeCall[communityID] - comNighttimeCall[communityID]) < -5) {
                n.setProperty("daytimeNighttimeProfile", "Nighttime");
            } else if ((comDaytimeCall[communityID] - comNighttimeCall[communityID]) > 5) {
                n.setProperty("daytimeNighttimeProfile", "Daytime");
            } else {
                n.setProperty("daytimeNighttimeProfile", "Average");
            }
            if (((double) comWeekDayCall[communityID] / 5 - (double) comWeekendCall[communityID] / 2) < -1) {
                n.setProperty("weekdayWeekendProfile", "Weekend");
            } else if (((double) comWeekDayCall[communityID] / 5 - (double) comWeekendCall[communityID] / 2) > 1) {
                n.setProperty("weekdayWeekendProfile", "Weekday");
            } else {
                n.setProperty("weekdayWeekendProfile", "Average");
            }
            nodes.add(n);
        }

        return nodes;
    }

}
