import java.util.Collection;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

public class Graph {
	
	private EdgeIterable edges;
	private NodeIterable nodes;
	
	public Graph(Set<Node> nodes, List<Edge> edges){
		this.nodes = new NodeIterable();
		this.edges = new EdgeIterable();
		for(Node n : nodes){
			this.nodes.add(n);
		}
		for(Edge e : edges){
			this.edges.add(e);
		}
	}
	
	public boolean addEdge(Edge edge){
		return edges.add(edge);
	}

	public boolean addNode(Node node){
		return nodes.add(node);
	}

	public boolean addAllEdges(Collection<? extends Edge> clctn){
		EdgeIterable tmp = edges;
		for(Edge e : clctn) {
			if(!edges.add(e)) {
				edges = tmp;
				return false;
			}
		}
		return true;
	}

	public boolean addAllNodes(Collection<? extends Node> clctn){
		NodeIterable tmp = nodes;
		for(Node n : clctn) {
			if(!nodes.add(n)){
				nodes = tmp;
				return false;
			}
		}
		return true;
	}

	public boolean removeEdge(Edge edge){
		return edges.removeEdge(edge);
	}

	public boolean removeNode(Node node){
		return nodes.removeNode(node);
	}

	public boolean removeAllEdges(Collection<? extends Edge> clctn){
		for (Edge e : clctn) {
			edges.removeEdge(e);
		}
		return true;
	}

	public boolean removeAllNodes(Collection<? extends Node> clctn){
		for (Node n : clctn) {
			nodes.removeNode(n);
		}
		return true;
	}

	public NodeIterable getNodes(){
		return nodes;
	}

	public EdgeIterable getEdges(){
		return edges;
	}

	public NodeIterable getNeighbors(Node node){
		NodeIterable res = new NodeIterable();
		Set<Node> tmp = new HashSet<Node>();
		for (Edge e : edges) {
			if(e.getSource() == node.getID()) {
//				res.add(nodes.get(e.getTarget()));
				tmp.add(nodes.get(e.getTarget()));
			} else if(e.getTarget() == node.getID()) {
//				res.add(nodes.get(e.getSource()));
				tmp.add(nodes.get(e.getSource()));
			}
		}
		for(Node n : tmp) {
			res.add(n);
		}
		return res;
	}
	
	public Edge getEdge(Node n, Node nei) {
		// TODO : Fill this function
		return null;
	}

	public int getNodeCount(){
		return nodes.count();
	}
}
