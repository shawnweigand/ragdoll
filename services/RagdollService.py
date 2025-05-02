import requests
import os
from dotenv import load_dotenv

load_dotenv()

class RagdollService:
    def __init__(self):
        self.base_url = 'localhost:80/api'
        self.app_token = os.getenv('RAGDOLL_APP_TOKEN')
        self.headers = {
            'APP-TOKEN': self.app_token
        } 

    # def get(params=None):
    #     url = f"{self.base_url}/chunk"
    #     try:
    #         response = requests.get(url, headers=self.headers, params=params)
    #         response.raise_for_status()
    #         return response.json()
    #     except requests.RequestException as e:
    #         print(f"[GET] Error calling {url}: {e}")
    #         return None

    def sendChunk(self, data=None):
        url = f"{self.base_url}/chunk"
        try:
            response = requests.post(url, json=data, headers=self.headers)
            response.raise_for_status()
            return response.json()
        except requests.RequestException as e:
            print(f"[POST] Error calling {url}: {e}")
            return None
