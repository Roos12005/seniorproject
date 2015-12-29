
public class Edge {
        int source;
        int target;
        float weight;

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
        
}
