from flask import Flask, render_template, render_template_string, request, session, redirect, url_for, abort
import secrets
import hashlib
import sqlite3
from functools import wraps
import re
import os

app = Flask(__name__)
app.secret_key = secrets.token_hex(32)

def init_db():
    conn = sqlite3.connect('database.db')
    c = conn.cursor()
    c.execute('''CREATE TABLE IF NOT EXISTS users
                 (id INTEGER PRIMARY KEY AUTOINCREMENT,
                  username TEXT UNIQUE NOT NULL,
                  password TEXT NOT NULL)''')
    c.execute('''CREATE TABLE IF NOT EXISTS urls
                 (id INTEGER PRIMARY KEY AUTOINCREMENT,
                  user_id INTEGER NOT NULL,
                  short_code TEXT UNIQUE NOT NULL,
                  original_url TEXT NOT NULL)''')
    
    admin_pass = secrets.token_hex(16) 
    admin_hash = hashlib.sha256(admin_pass.encode()).hexdigest()
    try:
        c.execute('INSERT INTO users (username, password) VALUES (?, ?)',
                  ('admin', admin_hash))
    except sqlite3.IntegrityError:
        pass  # Admin already exists
    
    conn.commit()
    conn.close()

def get_db():
    conn = sqlite3.connect('database.db')
    conn.row_factory = sqlite3.Row
    return conn


init_db()

def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_id' not in session:
            return redirect(url_for('login'))
        return f(*args, **kwargs)
    return decorated_function

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')
        
        if not username or not password:
            return "Username and password are required!"
            
        db = get_db()
        try:
            hashed_password = hashlib.sha256(password.encode()).hexdigest()
            db.execute('INSERT INTO users (username, password) VALUES (?, ?)',
                      (username, hashed_password))
            db.commit()
            return redirect(url_for('login'))
        except sqlite3.IntegrityError:
            return "Username already exists!"
        finally:
            db.close()
            
    return render_template('register.html')

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')
        
        db = get_db()
        user = db.execute('SELECT * FROM users WHERE username = ?', (username,)).fetchone()
        
        if user and user['password'] == hashlib.sha256(password.encode()).hexdigest():
            session['user_id'] = user['id']
            session['username'] = username
            session['is_admin'] = (username == 'admin')
            return redirect(url_for('dashboard'))
            
        return "Invalid credentials!"
    
    return render_template('login.html')

@app.route('/logout')
def logout():
    session.clear()
    return redirect(url_for('index'))

@app.route('/dashboard', methods=['GET', 'POST'])
@login_required
def dashboard():
    if request.method == 'POST':
        url = request.form.get('url')
        custom_code = request.form.get('custom_code', '').strip()
        
        if not url:
            return "URL is required!"
            
        if not custom_code:
            custom_code = secrets.token_urlsafe(6)
        elif not re.match("^[a-zA-Z0-9-_]+$", custom_code):
            return "Custom code can only contain letters, numbers, hyphens and underscores!"
            
        db = get_db()
        try:
            db.execute('INSERT INTO urls (user_id, short_code, original_url) VALUES (?, ?, ?)',
                      (session['user_id'], custom_code, url))
            db.commit()
        except sqlite3.IntegrityError:
            return "This custom code is already taken!"
        finally:
            db.close()
            
        return redirect(url_for('dashboard'))
        
    db = get_db()
    urls = db.execute('SELECT * FROM urls WHERE user_id = ?', (session['user_id'],)).fetchall()
    db.close()
    
    flag_section = ''
    if session.get('username') == 'admin':
        flag = os.getenv('DYN_FLAG', 'FlagY{test_flag}')
        flag_section = f'<div class="admin-section"><h3>Admin Section</h3><p>Flag: {flag}</p></div>'
    
    return render_template('dashboard.html', urls=urls, flag_section=flag_section)

@app.route('/<short_code>')
def redirect_url(short_code):
    blacklist = ['{{', 'class', 'attr', 'mro', '[', 'import', 'os', 'system', 
                'subclasses', 'mro', 'request', 'args', 'eval', 'exec']
    
    for word in blacklist:
        if word in short_code.lower():
            return "Forbidden characters in URL!"
    
    db = get_db()
    url = db.execute('SELECT original_url FROM urls WHERE short_code = ?', 
                    (short_code,)).fetchone()
    db.close()
    
    if url:
        return redirect(url['original_url'])
    abort(404)

@app.errorhandler(404)
def page_not_found(e):
    path = request.path.lstrip('/')
    template = '''
        <h1>404 - Page Not Found</h1>
        <p>The requested URL /''' + path + ''' was not found on this server.</p>
        <p>Please check the URL or go back to the <a href="/">homepage</a>.</p>
    '''
    return render_template_string(template), 404

if __name__ == '__main__':
    app.run(debug=True) 