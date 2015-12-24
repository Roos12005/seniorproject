
import com.opencsv.CSVReader;
import java.io.FileReader;
import java.io.IOException;
import java.util.*;

public class SocialNetworkAnalysis {
    public static void main(String[] args) throws IOException {
        Set<Node> nodes;
        List<Edge> edges;
        try (CSVReader reader = new CSVReader(new FileReader("testdata2.csv"))) {
            String [] nextLine;
            nodes = new HashSet<>();
            edges = new ArrayList<>();
            Node a,b;
            while ((nextLine = reader.readNext()) != null) {
                a = new Node(Integer.parseInt(nextLine[0]));
                b = new Node(Integer.parseInt(nextLine[1]));
                nodes.add(a);
                nodes.add(b);
                
                Edge e = new Edge(Integer.parseInt(nextLine[0]),Integer.parseInt(nextLine[1]),1);
                edges.add(e);
            }   
            System.out.println("Reading Data ... Done!");
        }
    	Graph hgraph = new Graph(nodes,edges);
        GraphDistance dis = new GraphDistance(hgraph);
        dis.execute(hgraph);
        
        Modularity mod = new Modularity(hgraph);
    	// Compute Modularity Class
    	int[] com = mod.buildCommunities(hgraph);
    	// TODO : Output com
    	int aa = 0;
    	Set<Integer> tot = new HashSet<Integer>();
    	for(int c : com) {
    		aa++;
    		tot.add(c);
    		System.out.println(aa + " : " + c);
    	}
    	System.out.println("-------------------------------------------");
    	System.out.println("Total of Communities : " + tot.size());
    	System.out.println("-------------------------------------------");
    	for(Integer co : tot) {
    		System.out.println(co);
    	}
        System.out.println("-------------------------------------------");
        System.out.println("Network Diameter");
        for(Node node : hgraph.getNodes()){
            System.out.println("Node " + node.getID() + " : BC = " + node.getBetweenness() + " CC = " + node.getCloseness() + " EC = " + node.getEccentricity());
        }
    }
}
