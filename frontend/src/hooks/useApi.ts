import { useState, useCallback } from 'react'

interface UseApiState<T> {
    data: T | null
    loading: boolean
    error: Error | null
}

export const useApi = <T,>(apiFunc: (...args: any[]) => Promise<T>) => {
    const [state, setState] = useState<UseApiState<T>>({
        data: null,
        loading: false,
        error: null,
    })

    const execute = useCallback(
        async (...args: any[]) => {
            setState({ data: null, loading: true, error: null })
            try {
                const result = await apiFunc(...args)
                setState({ data: result, loading: false, error: null })
                return result
            } catch (error) {
                setState({ data: null, loading: false, error: error as Error })
                throw error
            }
        },
        [apiFunc]
    )

    return { ...state, execute }
}
