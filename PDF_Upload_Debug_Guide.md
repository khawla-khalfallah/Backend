# 🔧 PDF Upload 403 Forbidden - Frontend Debugging Guide

## Common Causes & Solutions

### 1. 🔐 Authentication Issues
**Problem**: Token not sent or invalid token
**Solution**:
```javascript
// Make sure token is properly set in axios headers
const token = localStorage.getItem('token');
const formData = new FormData();
formData.append('titre', pdfTitle);
formData.append('formation_id', formationId);
formData.append('fichier', pdfFile);

axios.post('http://localhost:8000/api/pdfs', formData, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'multipart/form-data',
  }
})
```

### 2. 👤 User Role Issues
**Problem**: User is not a formateur or formateur not accepted
**Check**: 
```javascript
// Verify user role and status
const user = JSON.parse(localStorage.getItem('user'));
console.log('User role:', user.role);
console.log('Formateur status:', user.formateur?.status);
```

### 3. 📚 Formation Ownership
**Problem**: Formation doesn't belong to logged-in formateur
**Solution**: Only use formations that belong to the current formateur
```javascript
// Make sure formation_id belongs to current formateur
const myFormations = await axios.get('/api/formateurs/{formateur_id}/formations', {
  headers: { 'Authorization': `Bearer ${token}` }
});
```

### 4. 📄 File Format Issues
**Problem**: File is not a PDF or too large
**Check**:
```javascript
// Validate file before upload
const file = event.target.files[0];
if (!file.type === 'application/pdf') {
  alert('Seuls les fichiers PDF sont acceptés');
  return;
}
if (file.size > 100 * 1024 * 1024) { // 100MB
  alert('Le fichier est trop volumineux');
  return;
}
```

## 🕵️ Frontend Debugging Steps

### Step 1: Check Authentication
```javascript
// Add this before the PDF upload request
const token = localStorage.getItem('token');
console.log('Token exists:', !!token);
console.log('Token preview:', token?.substring(0, 20) + '...');

// Test if user is authenticated
try {
  const userResponse = await axios.get('http://localhost:8000/api/profile', {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  console.log('User profile:', userResponse.data);
} catch (error) {
  console.error('Authentication failed:', error.response?.data);
}
```

### Step 2: Verify Request Format
```javascript
// Log the exact request being sent
const formData = new FormData();
formData.append('titre', pdfTitle);
formData.append('formation_id', formationId);
formData.append('fichier', pdfFile);

console.log('Request data:');
for (let [key, value] of formData.entries()) {
  console.log(key, value);
}
```

### Step 3: Check Server Response
```javascript
try {
  const response = await axios.post('http://localhost:8000/api/pdfs', formData, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'multipart/form-data',
    }
  });
  console.log('Success:', response.data);
} catch (error) {
  console.error('Error details:');
  console.error('Status:', error.response?.status);
  console.error('Data:', error.response?.data);
  console.error('Headers:', error.response?.headers);
}
```

## 🚀 Complete Working Example

```javascript
const handlePdfUpload = async (pdfTitle, formationId, pdfFile) => {
  try {
    // 1. Get token
    const token = localStorage.getItem('token');
    if (!token) {
      throw new Error('Aucun token d\'authentification trouvé');
    }

    // 2. Validate file
    if (!pdfFile || pdfFile.type !== 'application/pdf') {
      throw new Error('Veuillez sélectionner un fichier PDF valide');
    }

    if (pdfFile.size > 100 * 1024 * 1024) { // 100MB
      throw new Error('Le fichier est trop volumineux (max 100MB)');
    }

    // 3. Prepare form data
    const formData = new FormData();
    formData.append('titre', pdfTitle);
    formData.append('formation_id', formationId);
    formData.append('fichier', pdfFile);

    // 4. Send request
    const response = await axios.post('http://localhost:8000/api/pdfs', formData, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data',
      }
    });

    console.log('PDF ajouté avec succès:', response.data);
    return response.data;

  } catch (error) {
    console.error('Erreur lors de l\'ajout du PDF:');
    
    if (error.response) {
      // Server responded with error status
      console.error('Status:', error.response.status);
      console.error('Data:', error.response.data);
      
      switch (error.response.status) {
        case 401:
          alert('Session expirée. Veuillez vous reconnecter.');
          // Redirect to login
          break;
        case 403:
          alert('Accès refusé: ' + (error.response.data.error || 'Non autorisé'));
          break;
        case 422:
          alert('Données invalides: ' + JSON.stringify(error.response.data.details || error.response.data.error));
          break;
        default:
          alert('Erreur: ' + (error.response.data.error || 'Erreur serveur'));
      }
    } else {
      console.error('Network or other error:', error.message);
      alert('Erreur de connexion: ' + error.message);
    }
    
    throw error;
  }
};
```

## 📋 Checklist Before Upload

- [ ] ✅ User is logged in (token exists)
- [ ] ✅ User role is 'formateur'
- [ ] ✅ Formateur status is 'accepte'
- [ ] ✅ Formation belongs to current formateur
- [ ] ✅ File is valid PDF
- [ ] ✅ File size < 100MB
- [ ] ✅ All required fields are filled
- [ ] ✅ Authorization header is correctly formatted

## 🐛 If Still Getting 403

1. Check the Laravel logs: `tail -f storage/logs/laravel.log`
2. The enhanced PdfController now logs detailed debug info
3. Look for specific error messages in the logs
4. Test with the provided HTTP test script to confirm backend works
5. Compare frontend request format with working backend test