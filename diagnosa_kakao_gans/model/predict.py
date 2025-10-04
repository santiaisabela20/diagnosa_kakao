# dummy content
import os
# Baris ini harus ada di paling atas sebelum import tensorflow
# Level '3' berarti semua log (Info, Warning, Error) akan disembunyikan
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3' 

import tensorflow as tf
from tensorflow.keras.preprocessing import image
import numpy as np
import sys
import json 

try:
    model = tf.keras.models.load_model('model_kakau_augmented.h5')
except Exception as e:
    print(json.dumps({'error': 'Gagal memuat model AI.'}))
    sys.exit()

CLASS_NAMES = ['black_pod_rot', 'healthy', 'pod_borer']
IMG_SIZE = (150, 150)

def prediksi_gambar(file_path):
    try:
        img = image.load_img(file_path, target_size=IMG_SIZE)
        img_array = image.img_to_array(img)
        img_batch = np.expand_dims(img_array, axis=0)
        
        # prediksi = model.predict(img_batch)
        prediksi = model.predict(img_batch, verbose=0) 
        skor = tf.nn.softmax(prediksi[0])
        
        hasil = CLASS_NAMES[np.argmax(skor)]
        kepercayaan = 100 * np.max(skor)
        
        result_dict = {
            'penyakit': hasil,
            'kepercayaan': float(kepercayaan) # ubah ke float agar valid untuk JSON
        }
        
        print(json.dumps(result_dict))

    except Exception as e:
        print(json.dumps({'error': str(e)}))

if __name__ == '__main__':
    if len(sys.argv) > 1:
        lokasi_gambar = sys.argv[1]
        prediksi_gambar(lokasi_gambar)
    else:
        print(json.dumps({'error': 'Tidak ada path gambar yang diberikan.'}))