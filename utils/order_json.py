import json
with open('bisac.json', 'r') as file:
    contents = file.read()

data = json.loads(contents)
 
sorted_data = {k: v for k, v in sorted(data.items(), key=lambda item: item[1]['name']) }

with open('sorted_bisac.json', 'w', encoding='utf-8') as file:
    json.dump(sorted_data, file, ensure_ascii=False, indent=4) 
