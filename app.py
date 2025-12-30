from flask import Flask, request, jsonify
import joblib
import pandas as pd
import numpy as np

# Define the class (same as in create_model.py)
class JobRecommenderSystem:
    def __init__(self, model):
        self.model = model
    
    def get_top_5_jobs(self, skills_input):
        skills_array = np.array(skills_input).reshape(1, -1)
        probabilities = self.model.predict_proba(skills_array)[0]
        
        job_probs = pd.DataFrame({
            'Job': self.model.classes_,
            'Probability': probabilities
        }).sort_values('Probability', ascending=False).head(5)
        
        return [(row['Job'], float(row['Probability'])) for _, row in job_probs.iterrows()]
    
    def get_5_soft_traits(self, skills_input):
        return {
            'Critical Thinking': int(skills_input[10]),
            'Problem Solving': int(skills_input[11]), 
            'Communication': int(skills_input[12]),
            'Teamwork': int(skills_input[13]),
            'Adaptability': int(skills_input[14])
        }

# Initialize Flask app
app = Flask(__name__)

# SIMPLE CORS - Only one place setting headers
@app.after_request
def after_request(response):
    response.headers.add('Access-Control-Allow-Origin', '*')
    response.headers.add('Access-Control-Allow-Headers', 'Content-Type')
    response.headers.add('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
    return response

@app.route('/recommend', methods=['POST', 'OPTIONS'])
def recommend_jobs():
    if request.method == 'OPTIONS':
        return '', 200
    
    if system is None:
        return jsonify({"error": "Model not loaded"}), 500
    
    try:
        # Get JSON data from request
        data = request.get_json()
        
        # Validate input
        if not data or 'skills' not in data:
            return jsonify({"error": "Missing 'skills' in request body"}), 400
        
        skills = data['skills']
        
        # Validate skills array
        if not isinstance(skills, list):
            return jsonify({"error": "Skills must be a list"}), 400
        
        if len(skills) != 15:
            return jsonify({"error": "Exactly 15 skills required (10 technical + 5 soft skills)"}), 400
        
        # Validate each skill rating
        for i, skill in enumerate(skills):
            if not isinstance(skill, int) or skill < 1 or skill > 5:
                return jsonify({"error": f"Skill {i+1} must be an integer between 1 and 5"}), 400
        
        # Get recommendations
        top_jobs = system.get_top_5_jobs(skills)
        soft_traits = system.get_5_soft_traits(skills)
        
        # Prepare response
        response = {
            "top_5_jobs": [
                {"job": job, "probability": prob} for job, prob in top_jobs
            ],
            "soft_traits": soft_traits
        }
        
        return jsonify(response)
    
    except Exception as e:
        return jsonify({"error": f"Internal server error: {str(e)}"}), 500

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        "status": "healthy",
        "model_loaded": system is not None
    })

@app.route('/', methods=['GET'])
def home():
    """Home endpoint with API usage instructions"""
    return jsonify({
        "message": "Job Recommender System API",
        "endpoints": {
            "POST /recommend": "Get job recommendations",
            "GET /health": "Health check"
        }
    })

# Load the model
try:
    system = joblib.load("job_recommender_system.pkl")
    print("Model loaded successfully!")
except FileNotFoundError:
    print("Error: job_recommender_system.pkl not found.")
    system = None

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)