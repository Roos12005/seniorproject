import java.util.*;

public class GraphDistance {
    public static final String BETWEENNESS = "betweennesscentrality";
    public static final String CLOSENESS = "closenesscentrality";
    public static final String ECCENTRICITY = "eccentricity";
    
    private double[] betweenness;
    private double[] closeness;
    private double[] eccentricity;
    
    private int diameter;
    private int radius;
    
    private double avgDist;
    private int N;
    
    private boolean isDirected;

    private boolean isCanceled;
    private int shortestPaths;
    private boolean isNormalized;
    
    public GraphDistance(Graph hgraph) {
        isCanceled = false;
        isDirected = true;
        N = hgraph.getNodeCount();
    }
    
    public double getPathLength() {
        return avgDist;
    }
    
    public double getDiameter() {
        return diameter;
    }
    
    public double getRadius() {
        return radius;
    }
    
    public Map<String, double[]> calculateDistanceMetrics(Graph hgraph, HashMap<Node, Integer> indicies,
                                                         boolean directed, boolean normalized) {
        int n = hgraph.getNodeCount();
        
        HashMap<String, double[]> metrics = new HashMap<>();
        
        double[] nodeEccentricity = new double[n];
        double[] nodeBetweenness = new double[n];
        double[] nodeCloseness = new double[n];
        
        metrics.put(ECCENTRICITY, nodeEccentricity);
        metrics.put(CLOSENESS, nodeCloseness);
        metrics.put(BETWEENNESS, nodeBetweenness);

        int count = 0;
               
        for (Node s : hgraph.getNodes()) {
            Stack<Node> S = new Stack<>();

            LinkedList<Node>[] P = new LinkedList[n];
            double[] theta = new double[n];
            int[] d = new int[n];
            
            int s_index = indicies.get(s);
            
            setInitParametetrsForNode(s, P, theta, d, s_index, n);

            LinkedList<Node> Q = new LinkedList<>();
            Q.addLast(s);
            while (!Q.isEmpty()) {
                Node v = Q.removeFirst();
                S.push(v);
                int v_index = indicies.get(v);

                EdgeIterable edgeIter = getEdgeIter(hgraph, v, directed);

                for (Edge edge : edgeIter) {
                    Node reachable = hgraph.getOpposite(v, edge);

                    int r_index = indicies.get(reachable);
                    if (d[r_index] < 0) {
                        Q.addLast(reachable);
                        d[r_index] = d[v_index] + 1;
                    }
                    if (d[r_index] == (d[v_index] + 1)) {
                        theta[r_index] = theta[r_index] + theta[v_index];
                        P[r_index].addLast(v);
                    }
                }
            }
            double reachable = 0;
            for (int i = 0; i < n; i++) {
                if (d[i] > 0) {
                    avgDist += d[i];
                    nodeEccentricity[s_index] = (int) Math.max(nodeEccentricity[s_index], d[i]);
                    nodeCloseness[s_index] += d[i];
                    diameter = Math.max(diameter, d[i]);
                    reachable++;
                }
            }

            radius = (int) Math.min(nodeEccentricity[s_index], radius);

            if (reachable != 0) {
                nodeCloseness[s_index] /= reachable;
            }

            shortestPaths += reachable;

            double[] delta = new double[n];
            while (!S.empty()) {
                Node w = S.pop();
                int w_index = indicies.get(w);
                ListIterator<Node> iter1 = P[w_index].listIterator();
                while (iter1.hasNext()) {
                    Node u = iter1.next();
                    int u_index = indicies.get(u);
                    delta[u_index] += (theta[u_index] / theta[w_index]) * (1 + delta[w_index]);
                }
                if (w != s) {
                    nodeBetweenness[w_index] += delta[w_index];
                }
            }
            
            count++;
            if (isCanceled) {
                return metrics;
            }
        }

        avgDist /= shortestPaths;

        calculateCorrection(hgraph, indicies, nodeBetweenness, nodeCloseness, directed, normalized);
        
        return metrics;
    }
    
    private void setInitParametetrsForNode(Node s, LinkedList<Node>[] P, double[] theta, int[] d, int index, int n) {           
            for (int j = 0; j < n; j++) {
                P[j] = new LinkedList<>();
                theta[j] = 0;
                d[j] = -1;
            }
            theta[index] = 1;
            d[index] = 0;
    }
    
    private EdgeIterable getEdgeIter(Graph hgraph, Node v, boolean directed) {
            EdgeIterable edgeIter;

            if (directed) {
                edgeIter = hgraph.getOutEdges(v);
            } else {
                edgeIter = hgraph.getEdges(v);
            }
            return edgeIter;
    }
    
     public  HashMap<Node, Integer> createIndiciesMap(Graph hgraph) {
       HashMap<Node, Integer> indicies = new HashMap<>();
        int index = 0;
        for (Node s : hgraph.getNodes()) {
            indicies.put(s, index);
            index++;
        } 
        return indicies;
    }
     
     public void initializeStartValues() {
        betweenness = new double[N];
        eccentricity = new double[N];
        closeness = new double[N];
        diameter = 0;
        avgDist = 0;
        shortestPaths = 0;
        radius = Integer.MAX_VALUE;
     }
     
     private void calculateCorrection(Graph hgraph, HashMap<Node, Integer> indicies,
             double[] nodeBetweenness, double[] nodeCloseness, boolean directed, boolean normalized) {
         
         int n = hgraph.getNodeCount();
         
         for (Node s : hgraph.getNodes()) {
            
             int s_index = indicies.get(s);

            if (!directed) {
                nodeBetweenness[s_index] /= 2;
            }
            if (normalized) {
                nodeCloseness[s_index] = (nodeCloseness[s_index] == 0) ? 0 : 1.0 / nodeCloseness[s_index];
                nodeBetweenness[s_index] /= directed ? (n - 1) * (n - 2) : (n - 1) * (n - 2) / 2;
            }     
         }
     }
     
     private void saveCalculatedValues(Graph hgraph, HashMap<Node, Integer> indicies, double[] nodeEccentricity, double[] nodeBetweenness, double[] nodeCloseness) {
        
        for (Node s : hgraph.getNodes()) {
            int s_index = indicies.get(s);
            
            s.setAttribute(ECCENTRICITY, nodeEccentricity[s_index]);
            s.setAttribute(CLOSENESS, nodeCloseness[s_index]);
            s.setAttribute(BETWEENNESS, nodeBetweenness[s_index]);
        }
    }

    public void setNormalized(boolean isNormalized) {
        this.isNormalized = isNormalized;
    }

    public boolean isNormalized() {
        return isNormalized;
    }

    public void setDirected(boolean isDirected) {
        this.isDirected = isDirected;
    }

    public boolean isDirected() {
        return isDirected;
    }

    public boolean cancel() {
        this.isCanceled = true;
        return true;
    }

    public void execute(Graph hgraph) {
        initializeStartValues();
        HashMap<Node, Integer> indicies = createIndiciesMap(hgraph);
        Map<String, double[]> metrics = calculateDistanceMetrics(hgraph, indicies, isDirected, isNormalized);
        eccentricity = metrics.get(ECCENTRICITY);
        closeness = metrics.get(CLOSENESS);
        betweenness = metrics.get(BETWEENNESS);
        
        saveCalculatedValues(hgraph, indicies, eccentricity, betweenness, closeness);
    }
}