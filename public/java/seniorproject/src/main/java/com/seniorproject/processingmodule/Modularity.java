/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.seniorproject.processingmodule;

import com.seniorproject.graphmodule.Edge;
import com.seniorproject.graphmodule.Graph;
import com.seniorproject.graphmodule.Node;
import java.util.HashMap;
import java.util.LinkedList;
import java.util.Set;

/**
 *
 * @author pperfectionist
 */
public class Modularity {
    public static final String MODULARITY_CLASS = "modularity_class";
    private CommunityStructure structure;
    private double resolution = 1.;
    
    public Modularity(Graph hgraph) {
    	this.structure = new Modularity.CommunityStructure(hgraph);
    }
    class CommunityStructure {
    	HashMap<Community, Float>[] nodeConnectionsWeight;
        HashMap<Community, Integer>[] nodeConnectionsCount;
        HashMap<Node, Integer> map;
        Community[] nodeCommunities;
        Graph graph;
        double[] weights;
        double graphWeightSum;
        LinkedList<Edge>[] topology;
        LinkedList<Community> communities;
        int N;
        HashMap<Integer, Community> invMap;

        CommunityStructure(Graph hgraph) {
            this.graph = hgraph;
            N = hgraph.getNodeCount();
            invMap = new HashMap<>();
            nodeConnectionsWeight = new HashMap[N];
            nodeConnectionsCount = new HashMap[N];
            nodeCommunities = new Community[N];
            map = new HashMap<>();
            topology = new LinkedList[N];
            communities = new LinkedList<>();
            int index = 0;
            weights = new double[N];
            for (Node node : hgraph.getNodes()) {
                map.put(node, index);
                nodeCommunities[index] = new Community(this);
                nodeConnectionsWeight[index] = new HashMap<>();
                nodeConnectionsCount[index] = new HashMap<>();
                weights[index] = 0;
                nodeCommunities[index].seed(index);
                Community hidden = new Community(structure);
                hidden.nodes.add(index);
                invMap.put(index, hidden);
                communities.add(nodeCommunities[index]);
                index++;
            }

            for (Node node : hgraph.getNodes()) {
                int node_index = map.get(node);
                topology[node_index] = new LinkedList<>();

                for (Node neighbor : hgraph.getNeighbors(node)) {
                    if (node == neighbor) {
                        continue;
                    }
                    int neighbor_index = map.get(neighbor);
                    float weight = 1; // TODO : change to actual weight

                    weights[node_index] += weight;
                    Edge me = new Edge(node_index, neighbor_index, weight);
                    topology[node_index].add(me);
                    Community adjCom = nodeCommunities[neighbor_index];
                    nodeConnectionsWeight[node_index].put(adjCom, weight);
                    nodeConnectionsCount[node_index].put(adjCom, 1);
                    nodeCommunities[node_index].connectionsWeight.put(adjCom, weight);
                    nodeCommunities[node_index].connectionsCount.put(adjCom, 1);
                    nodeConnectionsWeight[neighbor_index].put(nodeCommunities[node_index], weight);
                    nodeConnectionsCount[neighbor_index].put(nodeCommunities[node_index], 1);
                    nodeCommunities[neighbor_index].connectionsWeight.put(nodeCommunities[node_index], weight);
                    nodeCommunities[neighbor_index].connectionsCount.put(nodeCommunities[node_index], 1);
                    graphWeightSum += weight;
                }
            }
            graphWeightSum /= 2.0;
        }

        private void addNodeTo(int node, Community to) {
            to.add(node);
            nodeCommunities[node] = to;

            for (Edge e : topology[node]) {
                int neighbor;
                neighbor = e.getTarget();

                //Remove Node Connection to this community
                Float neighEdgesTo = nodeConnectionsWeight[neighbor].get(to);
                if (neighEdgesTo == null) {
                    nodeConnectionsWeight[neighbor].put(to, e.getWeight());
                } else {
                    nodeConnectionsWeight[neighbor].put(to, neighEdgesTo + e.getWeight());
                }
                Integer neighCountEdgesTo = nodeConnectionsCount[neighbor].get(to);
                if (neighCountEdgesTo == null) {
                    nodeConnectionsCount[neighbor].put(to, 1);
                } else {
                    nodeConnectionsCount[neighbor].put(to, neighCountEdgesTo + 1);
                }

                Community adjCom = nodeCommunities[neighbor];
                Float wEdgesto = adjCom.connectionsWeight.get(to);
                if (wEdgesto == null) {
                    adjCom.connectionsWeight.put(to, e.getWeight());
                } else {
                    adjCom.connectionsWeight.put(to, wEdgesto + e.getWeight());
                }

                Integer cEdgesto = adjCom.connectionsCount.get(to);
                if (cEdgesto == null) {
                    adjCom.connectionsCount.put(to, 1);
                } else {
                    adjCom.connectionsCount.put(to, cEdgesto + 1);
                }

                Float nodeEdgesTo = nodeConnectionsWeight[node].get(adjCom);
                if (nodeEdgesTo == null) {
                    nodeConnectionsWeight[node].put(adjCom, e.getWeight());
                } else {
                    nodeConnectionsWeight[node].put(adjCom, nodeEdgesTo + e.getWeight());
                }

                Integer nodeCountEdgesTo = nodeConnectionsCount[node].get(adjCom);
                if (nodeCountEdgesTo == null) {
                    nodeConnectionsCount[node].put(adjCom, 1);
                } else {
                    nodeConnectionsCount[node].put(adjCom, nodeCountEdgesTo + 1);
                }

                if (to != adjCom) {
                    Float comEdgesto = to.connectionsWeight.get(adjCom);
                    if (comEdgesto == null) {
                        to.connectionsWeight.put(adjCom, e.getWeight());
                    } else {
                        to.connectionsWeight.put(adjCom, comEdgesto + e.getWeight());
                    }

                    Integer comCountEdgesto = to.connectionsCount.get(adjCom);
                    if (comCountEdgesto == null) {
                        to.connectionsCount.put(adjCom, 1);
                    } else {
                        to.connectionsCount.put(adjCom, comCountEdgesto + 1);
                    }

                }
            }
        }

        private void removeNodeFrom(int node, Community from) {

            Community community = nodeCommunities[node];
            for (Edge e : topology[node]) {
                int neighbor = e.getTarget();

                ////////
                //Remove Node Connection to this community
                Float edgesTo = nodeConnectionsWeight[neighbor].get(community);
                int countEdgesTo = nodeConnectionsCount[neighbor].get(community);
                if (countEdgesTo - 1 == 0) {
                    nodeConnectionsWeight[neighbor].remove(community);
                    nodeConnectionsCount[neighbor].remove(community);
                } else {
                    nodeConnectionsWeight[neighbor].put(community, edgesTo - e.getWeight());
                    nodeConnectionsCount[neighbor].put(community, countEdgesTo - 1);
                }

                ///////////////////
                //Remove Adjacency Community's connection to this community
                Community adjCom = nodeCommunities[neighbor];
                Float oEdgesto = adjCom.connectionsWeight.get(community);
                int oCountEdgesto = adjCom.connectionsCount.get(community);
                if (oCountEdgesto - 1 == 0) {
                    adjCom.connectionsWeight.remove(community);
                    adjCom.connectionsCount.remove(community);
                } else {
                    adjCom.connectionsWeight.put(community, oEdgesto - e.getWeight());
                    adjCom.connectionsCount.put(community, oCountEdgesto - 1);
                }

                if (node == neighbor) {
                    continue;
                }

                if (adjCom != community) {
                    Float comEdgesto = community.connectionsWeight.get(adjCom);
                    Integer comCountEdgesto = community.connectionsCount.get(adjCom);
                    if (comCountEdgesto - 1 == 0) {
                        community.connectionsWeight.remove(adjCom);
                        community.connectionsCount.remove(adjCom);
                    } else {
                        community.connectionsWeight.put(adjCom, comEdgesto - e.getWeight());
                        community.connectionsCount.put(adjCom, comCountEdgesto - 1);
                    }
                }

                Float nodeEgesTo = nodeConnectionsWeight[node].get(adjCom);
                Integer nodeCountEgesTo = nodeConnectionsCount[node].get(adjCom);
                if (nodeCountEgesTo - 1 == 0) {
                    nodeConnectionsWeight[node].remove(adjCom);
                    nodeConnectionsCount[node].remove(adjCom);
                } else {
                    nodeConnectionsWeight[node].put(adjCom, nodeEgesTo - e.getWeight());
                    nodeConnectionsCount[node].put(adjCom, nodeCountEgesTo - 1);
                }

            }
            from.remove(node);
        }

        private void moveNodeTo(int node, Community to) {
            Community from = nodeCommunities[node];
            removeNodeFrom(node, from);
            addNodeTo(node, to);
        }

        private void zoomOut() {
            int M = communities.size();
            LinkedList<Edge>[] newTopology = new LinkedList[M];
            int index = 0;
            nodeCommunities = new Community[M];
            nodeConnectionsWeight = new HashMap[M];
            nodeConnectionsCount = new HashMap[M];
            HashMap<Integer, Community> newInvMap = new HashMap<>();
            for (int i = 0; i < communities.size(); i++) {//Community com : mCommunities) {
                Community com = communities.get(i);
                nodeConnectionsWeight[index] = new HashMap<>();
                nodeConnectionsCount[index] = new HashMap<>();
                newTopology[index] = new LinkedList<>();
                nodeCommunities[index] = new Community(com);
                Set<Community> iter = com.connectionsWeight.keySet();
                double weightSum = 0;

                Community hidden = new Community(structure);
                for (Integer nodeInt : com.nodes) {
                    Community oldHidden = invMap.get(nodeInt);
                    hidden.nodes.addAll(oldHidden.nodes);
                }
                newInvMap.put(index, hidden);
                for (Community adjCom : iter) {
                    int target = communities.indexOf(adjCom);
                    float weight = com.connectionsWeight.get(adjCom);
                    if (target == index) {
                        weightSum += 2. * weight;
                    } else {
                        weightSum += weight;
                    }
                    Edge e = new Edge(index, target, weight);
                    newTopology[index].add(e);
                }
                weights[index] = weightSum;
                nodeCommunities[index].seed(index);

                index++;
            }
            communities.clear();

            for (int i = 0; i < M; i++) {
                Community com = nodeCommunities[i];
                communities.add(com);
                for (Edge e : newTopology[i]) {
                    nodeConnectionsWeight[i].put(nodeCommunities[e.getTarget()], e.getWeight());
                    nodeConnectionsCount[i].put(nodeCommunities[e.getTarget()], 1);
                    com.connectionsWeight.put(nodeCommunities[e.getTarget()], e.getWeight());
                    com.connectionsCount.put(nodeCommunities[e.getTarget()], 1);
                }

            }

            N = M;
            topology = newTopology;
            invMap = newInvMap;
        }
    }
    
    class Community {
    	double weightSum;
        CommunityStructure structure;
        LinkedList<Integer> nodes;
        HashMap<Community, Float> connectionsWeight;
        HashMap<Community, Integer> connectionsCount;

        public int size() {
            return nodes.size();
        }
        
        

        public Community(Community com) {
            structure = com.structure;
            connectionsWeight = new HashMap<>();
            connectionsCount = new HashMap<>();
            nodes = new LinkedList<>();
            //mHidden = pCom.mHidden;
        }

        public Community(CommunityStructure structure) {
            this.structure = structure;
            connectionsWeight = new HashMap<>();
            connectionsCount = new HashMap<>();
            nodes = new LinkedList<>();
        }

    	public void seed(int node) {
            nodes.add(node);
            weightSum += structure.weights[node];
        }

        public boolean add(int node) {
            nodes.addLast(node);
            weightSum += structure.weights[node];
            return true;
        }

        public boolean remove(int node) {
            boolean result = nodes.remove(new Integer(node));
            weightSum -= structure.weights[node];
            if (nodes.isEmpty()) {
                structure.communities.remove(this);
            }
            return result;
        }
    }
    
    protected HashMap<String, Double> computeModularity(Graph hgraph, 
            CommunityStructure theStructure, int[] comStructure,double currentResolution) {

        HashMap<String, Double> results = new HashMap<>();

        boolean someChange = true;
        while (someChange) {
            someChange = false;
            boolean localChange = true;
            while (localChange) {
                localChange = false;
                int start = 0;

                int step = 0;
                for (int i = start; step < theStructure.N; i = (i + 1) % theStructure.N) {
                    step++;
                    Community bestCommunity = updateBestCommunity(theStructure, i, currentResolution);
                    if ((theStructure.nodeCommunities[i] != bestCommunity) && 
                            (bestCommunity != null)) {
                    	theStructure.moveNodeTo(i, bestCommunity);
                        localChange = true;
                    }
                }
                someChange = localChange || someChange;
            }

            if (someChange) {
                theStructure.zoomOut();
            }
        }

        fillComStructure(hgraph, theStructure, comStructure);
        return results;
    }

    Community updateBestCommunity(CommunityStructure theStructure, int i, double currentResolution) {
        double best = 0.;
        Community bestCommunity = null;
        Set<Community> iter = theStructure.nodeConnectionsWeight[i].keySet();
        for (Community com : iter) {
            double qValue = q(i, com, theStructure, currentResolution); // check q calculation
            if (qValue > best) {
                best = qValue;
                bestCommunity = com;
            }
        }
        return bestCommunity;
    }
    
    int[] fillComStructure(Graph hgraph, CommunityStructure theStructure, int[] comStructure) {
      int count = 0;

      for (Community com : theStructure.communities) {
          for (Integer node : com.nodes) {
              Community hidden = theStructure.invMap.get(node);
              for (Integer nodeInt : hidden.nodes) {
                  comStructure[nodeInt] = count;
              }
          }
          count++;
      }
      return comStructure;
    }
    
    private double q(int node, Community community, CommunityStructure theStructure, double currentResolution) {
        Float edgesToFloat = theStructure.nodeConnectionsWeight[node].get(community);
        double edgesTo = 0;
        if (edgesToFloat != null) {
            edgesTo = edgesToFloat.doubleValue();
        }
        double weightSum = community.weightSum;
        double nodeWeight = theStructure.weights[node];
        double qValue = currentResolution * edgesTo - (nodeWeight * weightSum) / (2.0 * theStructure.graphWeightSum);
        if ((theStructure.nodeCommunities[node] == community) && (theStructure.nodeCommunities[node].size() > 1)) {
            qValue = currentResolution * edgesTo - (nodeWeight * (weightSum - nodeWeight)) / (2.0 * theStructure.graphWeightSum);
        }
        if ((theStructure.nodeCommunities[node] == community) && (theStructure.nodeCommunities[node].size() == 1)) {
            qValue = 0.;
        }
        return qValue;
    }
    
    public int[] buildCommunities(Graph hgraph){
        int[] comStructure = new int[hgraph.getNodeCount()];
        computeModularity(hgraph, structure, comStructure, resolution);
        return comStructure;
    }
    
    public static void printGraph(Graph hgraph) {
    	for(Node n : hgraph.getNodes()) {
    		for(Node m : hgraph.getNeighbors(n)) {
    			System.out.println(n.getID() + " --> " + m.getID());
    		}
    		System.out.println();
    	}
    }    
}
