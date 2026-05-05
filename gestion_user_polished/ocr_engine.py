import os
from flask import Flask, request, jsonify
from flask_cors import CORS
import cv2
import numpy as np
import pytesseract
import re
import base64
from datetime import datetime

app = Flask(__name__)
CORS(app)

pytesseract.pytesseract.tesseract_cmd = r'C:\Program Files\Tesseract-OCR\tesseract.exe'
os.environ['TESSDATA_PREFIX'] = r'c:\xampp\htdocs\gestion_user\tessdata'

@app.route('/extract_document', methods=['POST'])
def extract_document():
    data = request.json
    if not data or 'image' not in data:
        return jsonify({'success': False, 'message': "Aucune image envoyée"})
    
    try:
        img_data = base64.b64decode(data['image'])
        np_arr = np.frombuffer(img_data, np.uint8)
        img = cv2.imdecode(np_arr, cv2.IMREAD_COLOR)

        # Prétraitement
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        gray = cv2.GaussianBlur(gray, (5, 5), 0)
        gray = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 11, 2)
        
        custom_config = r'--tessdata-dir c:/xampp/htdocs/gestion_user/tessdata'
        text = pytesseract.image_to_string(gray, lang='fra+ara+eng', config=custom_config)
        print("--- RAW OCR TEXT ---")
        print(text)
        print("--------------------")
        
        # Regex (very tolerant for demo purposes, and handling Tunisian CIN format loosely)
        nom_match = re.search(r'(?i)(?:NOM|Nom|NOw|NON)[\s:.]*([A-Z\s]+)', text)
        prenom_match = re.search(r'(?i)(?:PRENOM|PRÉNOM|Prenom|PRENOH|PRÉNON)[\s:.]*([A-Za-z\s]+)', text)
        date_match = re.search(r'\b(\d{2}[/\.]\d{2}[/\.]\d{4})\b', text)
        cin_match = re.search(r'\b(\d{8})\b', text)
        nat_match = re.search(r'(?i)(?:NATIONALITE|NATIONALITÉ)[\s:.]*([A-Za-z]+)', text)
        
        nom = nom_match.group(1).strip() if nom_match else ""
        prenom = prenom_match.group(1).strip() if prenom_match else ""
        date_naissance = date_match.group(1).replace('.', '/') if date_match else ""
        cin_number = cin_match.group(1) if cin_match else ""
        nationalite = nat_match.group(1).strip() if nat_match else ""
        
        # Calcul de confiance basique
        extracted = sum([bool(nom), bool(prenom), bool(date_naissance), bool(cin_number)])
        confiance = (extracted / 4) 
        
        if confiance < 0.5:
            return jsonify({'success': False, 'message': "Document illisible, veuillez reprendre la photo"})
            
        with open('ocr_log.txt', 'a') as f:
            f.write(f"[{datetime.now()}] Extraction: CIN={cin_number}, Nom={nom}\n")
            
        return jsonify({
            'success': True,
            'data': {
                'nom': nom,
                'prenom': prenom,
                'date_naissance': date_naissance,
                'cin_number': cin_number,
                'nationalite': nationalite,
                'confiance': confiance
            }
        })
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})

if __name__ == '__main__':
    app.run(port=5007)
