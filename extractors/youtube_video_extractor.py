def youtube_video_extractor(
        chunk,
        meta=[]
    ):
    """
    Extract data to be sent to Ragdoll service API body for YouTube video chunks.
    
    Args:
        chunk (Chunk): A single chunk split from a document.
        meta (list): List of metadata keys to include in the `meta` field.
    
    Returns:
        dict: A dictionary containing the extracted data.
    """
    
    # Extract chunk data
    data = {
        "name": chunk.metadata.get("title", "unknown"),
        "source": chunk.metadata.get("source"),
        "content": chunk.page_content,
        "meta": {k: v for k, v in chunk.metadata.items() if k in meta}
    }
    return data