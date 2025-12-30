# job_recommender.py
import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier

class JobRecommenderSystem:
    def __init__(self, model):
        self.model = model
    
    def get_top_5_jobs(self, skills_input):
        """Returns array of top 5 jobs with probabilities"""
        skills_array = np.array(skills_input).reshape(1, -1)
        probabilities = self.model.predict_proba(skills_array)[0]
        
        job_probs = pd.DataFrame({
            'Job': self.model.classes_,
            'Probability': probabilities
        }).sort_values('Probability', ascending=False).head(5)
        
        return [(row['Job'], float(row['Probability'])) for _, row in job_probs.iterrows()]
    
    def get_5_soft_traits(self, skills_input):
        """Returns dict of 5 soft traits"""
        return {
            'Critical Thinking': int(skills_input[10]),
            'Problem Solving': int(skills_input[11]), 
            'Communication': int(skills_input[12]),
            'Teamwork': int(skills_input[13]),
            'Adaptability': int(skills_input[14])
        }
    
    def recommend_all(self, skills_input):
        """Returns both jobs and traits in one call"""
        return {
            'top_jobs': self.get_top_5_jobs(skills_input),
            'soft_traits': self.get_5_soft_traits(skills_input)
        }