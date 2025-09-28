import React, { useState, useEffect } from 'react';
import axios from 'axios';

const ApprenantCertificates = () => {
  const [certificates, setCertificates] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchCertificates();
  }, []);

  const fetchCertificates = async () => {
    try {
      const token = localStorage.getItem('token');
      const response = await axios.get('/api/apprenant/certifications', {
        headers: { Authorization: `Bearer ${token}` }
      });
      setCertificates(response.data);
    } catch (err) {
      console.error('Erreur lors du chargement des certificats:', err);
      setError('Impossible de charger les certificats');
    } finally {
      setLoading(false);
    }
  };

  const downloadCertificate = async (certificateId) => {
    try {
      const token = localStorage.getItem('token');
      const response = await axios.get(`/api/apprenant/certifications/${certificateId}/download`, {
        headers: { Authorization: `Bearer ${token}` },
        responseType: 'blob'
      });
      
      // Create download link
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `certificat_${certificateId}.pdf`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (err) {
      console.error('Erreur lors du téléchargement:', err);
      alert('Erreur lors du téléchargement du certificat');
    }
  };

  if (loading) {
    return (
      <div className="container mt-4">
        <div className="d-flex justify-content-center">
          <div className="spinner-border" role="status">
            <span className="visually-hidden">Chargement...</span>
          </div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container mt-4">
        <div className="alert alert-danger" role="alert">
          <i className="fas fa-exclamation-triangle me-2"></i>
          {error}
        </div>
      </div>
    );
  }

  return (
    <div className="container mt-4">
      <div className="row">
        <div className="col-12">
          <div className="d-flex justify-content-between align-items-center mb-4">
            <h2 className="mb-0">
              <i className="fas fa-certificate text-primary me-2"></i>
              Mes Certificats
            </h2>
          </div>

          {certificates.length === 0 ? (
            <div className="card text-center py-5">
              <div className="card-body">
                <i className="fas fa-certificate fa-3x text-muted mb-3"></i>
                <h5 className="text-muted">Aucun certificat disponible</h5>
                <p className="text-muted">
                  Vous obtiendrez des certificats en réussissant vos examens avec la note requise.
                </p>
              </div>
            </div>
          ) : (
            <div className="row">
              {certificates.map(certificate => (
                <div key={certificate.id} className="col-md-6 col-lg-4 mb-4">
                  <div className="card h-100 shadow-sm border-0">
                    <div className="card-header bg-gradient-primary text-white">
                      <div className="d-flex justify-content-between align-items-center">
                        <h6 className="mb-0 text-truncate me-2">
                          <i className="fas fa-award me-2"></i>
                          Certificat #{certificate.id.toString().padStart(6, '0')}
                        </h6>
                        <span className="badge bg-success">
                          <i className="fas fa-check me-1"></i>
                          Validé
                        </span>
                      </div>
                    </div>
                    
                    <div className="card-body">
                      <h5 className="card-title text-primary mb-2">
                        {certificate.titre_certification}
                      </h5>
                      
                      <div className="mb-3">
                        <h6 className="text-muted mb-1">Formation:</h6>
                        <p className="mb-0 fw-bold">{certificate.formation?.titre}</p>
                      </div>

                      <div className="row text-center mb-3">
                        <div className="col-6">
                          <div className="border-end">
                            <div className="fw-bold text-success fs-5">
                              {parseFloat(certificate.note_examen).toFixed(1)}/20
                            </div>
                            <small className="text-muted">Note obtenue</small>
                          </div>
                        </div>
                        <div className="col-6">
                          <div className="fw-bold text-primary">
                            {new Date(certificate.date_obtention).toLocaleDateString('fr-FR')}
                          </div>
                          <small className="text-muted">Date d'obtention</small>
                        </div>
                      </div>

                      {certificate.formateur && (
                        <div className="mb-3">
                          <small className="text-muted">Formateur:</small>
                          <div className="fw-semibold">
                            {certificate.formateur.user?.prenom} {certificate.formateur.user?.nom}
                          </div>
                        </div>
                      )}
                    </div>
                    
                    <div className="card-footer bg-transparent border-0">
                      <button
                        className="btn btn-primary w-100"
                        onClick={() => downloadCertificate(certificate.id)}
                      >
                        <i className="fas fa-download me-2"></i>
                        Télécharger PDF
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ApprenantCertificates;