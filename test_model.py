# test.py
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

def get_user_skills():
    """Get all 15 skills from user input"""
    skills = []
    
    technical_skills = [
        "Programming", "Database Management", "Networking", "Cybersecurity",
        "Data Analysis", "Machine Learning/AI", "Web Development",
        "System Administration", "Cloud Computing", "Software Testing/QA"
    ]
    
    soft_skills = [
        "Critical Thinking", "Problem Solving", "Communication", 
        "Teamwork", "Adaptability"
    ]
    
    print("Enter skills (1-5):")
    
    # Technical skills
    for i, skill in enumerate(technical_skills, 1):
        while True:
            try:
                rating = int(input(f"{i}. {skill}: "))
                if 1 <= rating <= 5:
                    skills.append(rating)
                    break
                else:
                    print("Enter 1-5")
            except ValueError:
                print("Enter a number")
    
    # Soft skills
    for i, skill in enumerate(soft_skills, 1):
        while True:
            try:
                rating = int(input(f"{i+10}. {skill}: "))
                if 1 <= rating <= 5:
                    skills.append(rating)
                    break
                else:
                    print("Enter 1-5")
            except ValueError:
                print("Enter a number")
    
    return skills

# Load the system
system = joblib.load("job_recommender_system.pkl")

# Get user input
user_skills = get_user_skills()

# Get the arrays
top_jobs_array = system.get_top_5_jobs(user_skills)
soft_traits_dict = system.get_5_soft_traits(user_skills)

# Print ONLY the arrays (no formatting)
print("\nTOP_5_JOBS_ARRAY:", top_jobs_array)
print("SOFT_TRAITS_DICT:", soft_traits_dict)