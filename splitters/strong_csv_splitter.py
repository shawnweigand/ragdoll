import csv
from io import StringIO
from collections import defaultdict
from datetime import datetime

def split_strong_csv_docs(docs):
    """
    Split Strong documents into smaller chunks based on the workout date.

    Args:
        docs (List[Document]): A list of documents to split.

    Returns:
        List[Chunk]: A list of split documents.
    """

    chunks = []

    for doc in docs:
        f = StringIO(doc.page_content)
        reader = csv.DictReader(f)

        grouped = defaultdict(list)

        for row in reader:
            key = (row['Date'], row['Workout Name'])
            grouped[key].append(row)
        
        for (date_raw, workout_name), rows in grouped.items():
            dt = datetime.strptime(date_raw, "%Y-%m-%d %H:%M:%S")
            readable_date = dt.strftime("%B %d, %Y at %I:%M %p")

            exercises = defaultdict(list)

            for row in rows:
                ex = row['Exercise Name']
                set_order = row['Set Order']
                weight = row['Weight']
                reps = row['Reps']
                line = f"   - Set {set_order}: {weight} lb x {reps} reps"
                exercises[ex].append(line)
                
            text = f"Workout: {workout_name}\nDate: {readable_date}\nDuration: {rows[0]['Duration']}\n\nExercises:\n"
            for ex_name, sets in exercises.items():
                text += f"{ex_name}\n" + "\n".join(sets) + "\n\n"

            chunks.append({
                "page_content": text.strip(),
                "metadata": {
                    "source": doc.metadata['source'],
                    "title": doc.metadata.get('title', ''),
                    "when": readable_date
                }
            })
    
    return chunks