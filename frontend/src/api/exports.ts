import { apiClient } from './client'

interface ExportParams {
    startDate?: string  
    endDate?: string    
    teamId?: number
    userId?: number
}

/**
 * Download a file from blob data
 */
const downloadFile = (blob: Blob, filename: string) => {
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
}

/**
 * Export clocking data to PDF
 * @param params Export parameters
 * @param download If true, automatically download. If false, return blob for preview
 */
export const exportClockingPdf = async (params: ExportParams = {}, download: boolean = true): Promise<{ blob: Blob, filename: string }> => {
    const queryParams = new URLSearchParams()

    if (params.startDate) queryParams.append('start_date', params.startDate)
    if (params.endDate) queryParams.append('end_date', params.endDate)
    if (params.teamId) queryParams.append('team_id', params.teamId.toString())
    if (params.userId) queryParams.append('user_id', params.userId.toString())

    try {
        const response = await apiClient.get(
            `/exports/clocking/pdf?${queryParams.toString()}`,
            {
                responseType: 'blob',
            }
        )

        const contentDisposition = response.headers['content-disposition']
        let filename = 'clocking_report.pdf'

        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="?([^"]+)"?/)
            if (filenameMatch) {
                filename = filenameMatch[1]
            }
        }

        if (download) {
            downloadFile(response.data, filename)
        }

        return { blob: response.data, filename }
    } catch (error: any) {
        if (error.response?.data instanceof Blob) {
            try {
                const text = await error.response.data.text()
                const jsonError = JSON.parse(text)
                error.response.data = jsonError
            } catch (parseError) {
                console.error('Failed to parse blob error:', parseError)
            }
        }
        throw error
    }
}

/**
 * Export clocking data to XLSX
 */
export const exportClockingXlsx = async (params: ExportParams = {}): Promise<void> => {
    const queryParams = new URLSearchParams()

    if (params.startDate) queryParams.append('start_date', params.startDate)
    if (params.endDate) queryParams.append('end_date', params.endDate)
    if (params.teamId) queryParams.append('team_id', params.teamId.toString())
    if (params.userId) queryParams.append('user_id', params.userId.toString())

    try {
        const response = await apiClient.get(
            `/exports/clocking/xlsx?${queryParams.toString()}`,
            {
                responseType: 'blob',
            }
        )

        const contentDisposition = response.headers['content-disposition']
        let filename = 'clocking_report.xlsx'

        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="?([^"]+)"?/)
            if (filenameMatch) {
                filename = filenameMatch[1]
            }
        }

        downloadFile(response.data, filename)
    } catch (error: any) {
        if (error.response?.data instanceof Blob) {
            try {
                const text = await error.response.data.text()
                const jsonError = JSON.parse(text)
                error.response.data = jsonError
            } catch (parseError) {
                console.error('Failed to parse blob error:', parseError)
            }
        }
        throw error
    }
}
