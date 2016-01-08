
public class Node {
	private int ID;
        private double eccentricity;
        private double closeness;
        private double betweenness;
	
	public Node(int s) {
		this.ID = s;
                this.betweenness = 0;
                this.closeness = 0;
                this.eccentricity = 0;
	}
	
	public int getID() {
		return ID;
	}

	public void setID(int id) {
		this.ID = id;
	}
	
        public double getEccentricity() {
            return this.eccentricity;
        }
        
        public void setEccentricity(double eccentricity) {
            this.eccentricity = eccentricity;
        }
        
        public double getCloseness() {
            return this.closeness;
        }
        
        public void setCloseness(double closeness) {
            this.closeness = closeness;
        }
        
        public double getBetweenness() {
            return this.betweenness;
        }
        
        public void setBetweenness(double betweenness) {
            this.betweenness = betweenness;
        }
        
        public void setAttribute(String type, double value) {
            if(type.equals("eccentricity")) {
                this.setEccentricity(value);
            } else if(type.equals("closenesscentrality")) {
                this.setCloseness(value);
            } else  if(type.equals("betweennesscentrality")) {
                this.setBetweenness(value);
            }
        }
        
	public boolean equals(Object o) {
	    return (o instanceof Node) && (((Node) o).getID()) == (this.getID());
	}
	
	public int hashCode() {
	    return (ID + "").hashCode();
	}
	
}
