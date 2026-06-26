#!/usr/bin/env python3
import json
import sys
import warnings
from datetime import datetime
from pathlib import Path


def result(status, label, score, message, metrics=None):
    return {
        "status": status,
        "label": label,
        "score": score,
        "message": message,
        "metrics": metrics or {},
        "createdAt": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "engine": "python-face-recognition",
    }


def print_result(payload):
    print(json.dumps(payload, ensure_ascii=False, separators=(",", ":")))


def largest_face(locations):
    return max(locations, key=lambda box: max(0, box[2] - box[0]) * max(0, box[1] - box[3]))


def face_encoding(face_recognition, image_path, missing_status, missing_label):
    image = face_recognition.load_image_file(str(image_path))
    locations = face_recognition.face_locations(image, model="hog")

    if not locations:
        return None, result(
            missing_status,
            missing_label,
            None,
            "Nenhum rosto foi detectado na imagem.",
            {"rostos_detectados": 0},
        )

    face = largest_face(locations)
    encodings = face_recognition.face_encodings(image, known_face_locations=[face], num_jitters=1)

    if not encodings:
        return None, result(
            missing_status,
            missing_label,
            None,
            "O rosto foi detectado, mas não foi possível gerar a assinatura facial.",
            {"rostos_detectados": len(locations)},
        )

    return encodings[0], None


def main():
    if len(sys.argv) != 3:
        print_result(result("indisponivel", "Indisponível", None, "Informe a foto capturada e a foto cadastrada."))
        return 2

    captured_path = Path(sys.argv[1])
    student_path = Path(sys.argv[2])

    if not captured_path.is_file():
        print_result(result("sem_captura", "Sem captura", None, "A foto capturada não foi encontrada."))
        return 1

    if not student_path.is_file():
        print_result(result("sem_foto_aluno", "Sem foto do aluno", None, "A foto cadastrada do aluno não foi encontrada."))
        return 1

    try:
        warnings.filterwarnings("ignore", message="pkg_resources is deprecated as an API.*", category=UserWarning)
        import face_recognition
    except Exception as exc:
        print_result(result("indisponivel", "Indisponível", None, f"Biblioteca face_recognition indisponível: {exc}"))
        return 1

    try:
        captured_encoding, captured_error = face_encoding(
            face_recognition,
            captured_path,
            "sem_rosto_captura",
            "Sem rosto",
        )
        if captured_error:
            print_result(captured_error)
            return 0

        student_encoding, student_error = face_encoding(
            face_recognition,
            student_path,
            "sem_rosto_aluno",
            "Sem rosto aluno",
        )
        if student_error:
            print_result(student_error)
            return 0

        distance = float(face_recognition.face_distance([student_encoding], captured_encoding)[0])
        score = round(max(0.0, min(100.0, (1.0 - distance) * 100.0)), 2)

        if distance <= 0.50:
            status = "compativel"
            label = "Compatível"
            message = "Rosto compatível com a foto cadastrada."
        elif distance <= 0.60:
            status = "verificar"
            label = "Verificar"
            message = "Similaridade intermediária; recomenda-se conferência manual."
        else:
            status = "divergente"
            label = "Divergente"
            message = "Rosto divergente da foto cadastrada."

        print_result(result(status, label, score, message, {"distancia": round(distance, 4)}))
        return 0
    except Exception as exc:
        print_result(result("indisponivel", "Indisponível", None, f"Erro ao comparar as imagens: {exc}"))
        return 1


if __name__ == "__main__":
    sys.exit(main())
