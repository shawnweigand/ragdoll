from services.YoutubeService import YoutubeService

yt = YoutubeService()

videos = yt.getChannelVideos('UCI5d0aR9R0AWgTRUkQ-K3UA')

vids = [
    {
        "videoId": video["id"]["videoId"],
        "title": video["snippet"]["title"]
    }
    for video in videos
]

print(vids)