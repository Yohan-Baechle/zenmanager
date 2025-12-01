const TOKEN_KEY = 'auth_token'

export const tokenUtils = {
    get: (): string | null => {
        return localStorage.getItem(TOKEN_KEY)
    },

    set: (token: string): void => {
        localStorage.setItem(TOKEN_KEY, token)
    },

    remove: (): void => {
        localStorage.removeItem(TOKEN_KEY)
    },

    decode: (token: string): any => {
        try {
            const base64Url = token.split('.')[1]
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/')
            const jsonPayload = decodeURIComponent(
                atob(base64)
                    .split('')
                    .map((c) => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
                    .join('')
            )
            return JSON.parse(jsonPayload)
        } catch (error) {
            return null
        }
    },

    isExpired: (token: string): boolean => {
        const decoded = tokenUtils.decode(token)
        if (!decoded || !decoded.exp) return true
        return decoded.exp * 1000 < Date.now()
    },
}
