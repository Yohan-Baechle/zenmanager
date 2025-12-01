import {useEffect, useRef, useState} from 'react'
import { exportClockingXlsx } from '../../api/exports'

interface ExportParams {
    startDate?: string
    endDate?: string
    teamId?: number
    userId?: number
}

interface PdfPreviewModalProps {
    isOpen: boolean
    onClose: () => void
    pdfBlob: Blob | null
    filename: string
    exportParams?: ExportParams
    onExportError?: (error: any, defaultMessage: string) => void
}

export default function PdfPreviewModal({
    isOpen,
    onClose,
    pdfBlob,
    filename,
    exportParams = {},
    onExportError
}: PdfPreviewModalProps) {
    const iframeRef = useRef<HTMLIFrameElement>(null)
    const pdfUrlRef = useRef<string | null>(null)
    const [exportingXlsx, setExportingXlsx] = useState(false)

    useEffect(() => {
        if (pdfBlob && isOpen) {
            // Create object URL for PDF
            pdfUrlRef.current = window.URL.createObjectURL(pdfBlob)

            // Set iframe source
            if (iframeRef.current) {
                iframeRef.current.src = pdfUrlRef.current
            }
        }

        // Cleanup
        return () => {
            if (pdfUrlRef.current) {
                window.URL.revokeObjectURL(pdfUrlRef.current)
                pdfUrlRef.current = null
            }
        }
    }, [pdfBlob, isOpen])

    const handleExportXlsx = async () => {
        setExportingXlsx(true)
        try {
            await exportClockingXlsx(exportParams)
        } catch (error: any) {
            if (onExportError) {
                onExportError(error, 'Erreur lors de la génération du fichier Excel')
            } else {
                console.error('Excel export error:', error)
            }
        } finally {
            setExportingXlsx(false)
        }
    }

    if (!isOpen) return null

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div className="bg-[var(--c1)] rounded-[20px] shadow-2xl w-full max-w-6xl h-[90vh] flex flex-col">
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-[var(--c2)]">
                    <h2 className="text-xl font-bold">Aperçu du PDF</h2>
                    <div className="flex gap-3">
                        <button
                            onClick={handleExportXlsx}
                            disabled={exportingXlsx}
                            className="px-6 py-3 rounded-xl font-medium font-semibold transition-colors bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {exportingXlsx ? 'Génération...' : 'Télécharger Excel'}
                        </button>
                        <button
                            onClick={onClose}
                            className="px-4 py-2 rounded-xl font-medium transition-colors bg-[var(--c2)] text-[var(--c5)] hover:bg-[var(--c2)]/75"
                        >
                            Fermer
                        </button>
                    </div>
                </div>

                {/* PDF Viewer */}
                <div className="flex-1 p-4 overflow-hidden">
                    <iframe
                        ref={iframeRef}
                        className="w-full h-full rounded-xl border border-[var(--c2)]"
                        title="PDF Preview"
                    />
                </div>
            </div>
        </div>
    )
}
