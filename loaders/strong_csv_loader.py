from langchain.schema import Document

def load_strong_csv_docs(source: str):
    """
    Load a document from a CSV export of workouts from the Strong app.
    
    Args:
        source (str): The name of the CSV file to load.
        
    Returns:
        List[Document]: A list of loaded documents.
    """
    
    # Read entire CSV file as plain text
    with open(f".csv/{source}", 'r', encoding='utf-8') as f:
        csv_text = f.read()

    # Wrap as one LangChain Document
    document = Document(
        page_content=csv_text, 
        metadata={
            "source": source,
            "title": source
        }
    )
    
    return [document]