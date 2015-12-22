import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;

public class NodeIterable implements Iterable<Node>, Iterator<Node> {
	private List<Node> nodes;
	private int count = -1;
	
	public NodeIterable(){
		this.nodes = new ArrayList<Node>();
	}
	
	public boolean add(Node n){
		return nodes.add(n);
	}
	
	
	@Override
	public Iterator<Node> iterator() {
		count = -1;
		return this;
	}

	@Override
	public boolean hasNext() {
		return count < (nodes.size() - 1);
	}

	@Override
	public Node next() {
		count++;
		return nodes.get(count);
	}


	public boolean removeNode(Node node) {
		int idx = nodes.indexOf(node);
		if (idx > -1) {
			nodes.remove(idx);
			return true;
		}
		return false;
	}


	public int count() {
		return nodes.size();
	}


	public Node get(int source) {
		for(Node n : nodes) {
			if(n.getID() == source) {
				return n;
			}
		}
		return null;
	}

}
