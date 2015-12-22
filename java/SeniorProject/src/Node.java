
public class Node {
	private int ID;
	
	public Node(int s) {
		this.ID = s;
	}
	
	public int getID() {
		return ID;
	}

	public void setID(int id) {
		this.ID = id;
	}
	
	public boolean equals(Object o) {
	    return (o instanceof Node) && (((Node) o).getID()) == (this.getID());
	}
	
	public int hashCode() {
	    return (ID + "").hashCode();
	}
	
}
