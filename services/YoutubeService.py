import requests
import os
from dotenv import load_dotenv

load_dotenv()

class YoutubeService:
    def __init__(self):
        self.base_url = 'https://www.googleapis.com/youtube/v3'
        self.api_token = os.getenv('YOUTUBE_API_KEY')

    def getChannelVideos(self, channelId):
        url = f"{self.base_url}/search"
        params = {
            "key": self.api_token,
            "part": "snippet",
            "channelId": channelId,
            "type": "video",
            "relevanceLanguage": "en",
            "maxResults": 50
        }

        all_videos = []
        next_page_token = None

        try:
            while True:
                if next_page_token:
                    params["pageToken"] = next_page_token
                else:
                    params.pop("pageToken", None)

                response = requests.get(url, params=params)
                response.raise_for_status()
                data = response.json()

                all_videos.extend(data.get("items", []))

                next_page_token = data.get("nextPageToken")

                if not next_page_token:
                    break  # Exit loop when there's no next page
            
            return all_videos

        except requests.RequestException as e:
            print(f"[GET] Error calling {url}: {e}")
            return None        
