def google_drive_extractor(
        chunk
    ):
    """
    Extract data to be sent to Ragdoll service API body for Google Drive chunks.
    
    Args:
        chunk (Chunk): A single chunk split from a document.
    
    Returns:
        dict: A dictionary containing the extracted data.
    """
    
    # Extract chunk data
    data = {
        "name": chunk.metadata.get("title", "unknown"),
        "source": chunk.metadata.get("source"),
        "content": chunk.page_content,
    }
    return data