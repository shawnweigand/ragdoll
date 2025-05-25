from splitters.recursive_splitter import split_recursive_docs
from extractors.youtube_video_extractor import youtube_video_extractor
from loaders.youtube_video_loader import load_youtube_video_transcript
from utils.load_and_split_docs import load_and_split_docs

load_and_split_docs(
    None,                           #Arg: parent_id
    "YouTubeVideo",                 #Arg: document_type
    load_youtube_video_transcript,  #Arg: load_fn
    ["tgaTYwlHk30"],                #Arg: load_params
    split_recursive_docs,           #Arg: split_fn
    youtube_video_extractor,        #Arg: extract_fn
    [],                           #Arg: meta
    {}                            #Arg: tags
)