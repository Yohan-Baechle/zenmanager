import { useEffect, useState, useMemo } from "react";
import { toast } from "sonner";
import { clocksApi } from "../../api/clocks.api";
import type { ClockRequest } from "../../types/clock.types";
import Card from "../../components/common/Card";
import Loader from "../../components/common/Loader";
import Select from "../../components/common/Select";
import ClockRequestList from "../../components/features/clocks/ClockRequestList";
import ClockRequestReviewModal from "../../components/features/clocks/ClockRequestReviewModal";
import { PendingIcon } from "../../assets/icons/pending";
import { ArrowBackIosNewIcon } from "../../assets/icons/arrow-back-ios-new";

export default function ClockRequestsManagementPage() {
  const [clockRequests, setClockRequests] = useState<ClockRequest[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedRequest, setSelectedRequest] = useState<ClockRequest | null>(
    null
  );
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [startDate, setStartDate] = useState("");
  const [endDate, setEndDate] = useState("");
  const [statusFilter, setStatusFilter] = useState<
    "ALL" | "PENDING" | "APPROVED" | "REJECTED"
  >("PENDING");
  const itemsPerPage = 10;

  useEffect(() => {
    loadClockRequests();
  }, []);

  const loadClockRequests = async () => {
    try {
      setLoading(true);
      const data = await clocksApi.getClocksRequest();
      setClockRequests(data as any);
    } catch (error) {
      console.error("Failed to load clock requests", error);
      toast.error("Échec du chargement des demandes de pointage");
    } finally {
      setLoading(false);
    }
  };

  const handleReview = (request: ClockRequest) => {
    setSelectedRequest(request);
    setIsModalOpen(true);
  };

  const handleModalClose = () => {
    setIsModalOpen(false);
    setSelectedRequest(null);
  };

  const handleSuccess = () => {
    loadClockRequests();
  };

  const filteredClockRequests = useMemo(() => {
    let filtered = [...clockRequests];

    if (statusFilter !== "ALL") {
      filtered = filtered.filter((request) => request.status === statusFilter);
    }

    if (startDate) {
      filtered = filtered.filter(
        (request) => new Date(request.requestedTime) >= new Date(startDate)
      );
    }

    if (endDate) {
      filtered = filtered.filter(
        (request) =>
          new Date(request.requestedTime) <= new Date(endDate + "T23:59:59")
      );
    }

    return filtered.sort(
      (a, b) =>
        new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
    );
  }, [clockRequests, startDate, endDate, statusFilter]);

  const totalPages = Math.ceil(filteredClockRequests.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const paginatedClockRequests = filteredClockRequests.slice(
    startIndex,
    startIndex + itemsPerPage
  );

  const handlePrevious = () => {
    if (currentPage > 1) setCurrentPage(currentPage - 1);
  };

  const handleNext = () => {
    if (currentPage < totalPages) setCurrentPage(currentPage + 1);
  };

  if (loading) return <Loader />;

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold text-[var(--c5)]">
        Gestion des demandes de pointage
      </h1>

      <Card
        title="Demandes de pointage manuel"
        icon={PendingIcon}
        description="Gérez les demandes de pointage manuel de vos employés"
      >
        <div className="flex flex-wrap items-center gap-3 mb-4">
          <div className="flex items-center text-sm text-[var(--c5)]">
            <button
              onClick={handlePrevious}
              disabled={currentPage === 1}
              className="p-2 rounded-s-xl bg-[var(--c2)]/50 hover:bg-[var(--c2)]/75 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            >
              <ArrowBackIosNewIcon className="h-5 w-5" />
            </button>
            <div className="font-medium h-9 p-2 flex items-center bg-[var(--c2)]/50 border-l border-r border-[var(--c2)]">
              {currentPage}/{totalPages || 1}
            </div>
            <button
              onClick={handleNext}
              disabled={currentPage === totalPages || totalPages === 0}
              className="p-2 rounded-e-xl bg-[var(--c2)]/50 hover:bg-[var(--c2)]/75 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            >
              <ArrowBackIosNewIcon className="h-5 w-5 rotate-180" />
            </button>
          </div>

          <div className="w-48">
            <Select
              value={statusFilter}
              onChange={(e) => {
                setStatusFilter(e.target.value as "ALL" | "PENDING" | "APPROVED" | "REJECTED");
                setCurrentPage(1);
              }}
              options={[
                { value: "PENDING", label: `En attente (${clockRequests.filter((r) => r.status === "PENDING").length})` },
                { value: "APPROVED", label: `Approuvées (${clockRequests.filter((r) => r.status === "APPROVED").length})` },
                { value: "REJECTED", label: `Rejetées (${clockRequests.filter((r) => r.status === "REJECTED").length})` },
                { value: "ALL", label: `Toutes (${clockRequests.length})` },
              ]}
            />
          </div>

          <div className="flex items-center gap-2 text-sm text-[var(--c5)]">
            <span className="text-[var(--c4)]">Du</span>
            <input
              type="date"
              value={startDate}
              onChange={(e) => {
                setStartDate(e.target.value);
                setCurrentPage(1);
              }}
              className="focus:outline-none bg-[var(--c2)]/50 text-[var(--c5)] p-2 rounded-xl cursor-pointer border border-[var(--c3)] hover:border-[var(--c4)]"
            />
            <span className="text-[var(--c4)]">Au</span>
            <input
              type="date"
              value={endDate}
              onChange={(e) => {
                setEndDate(e.target.value);
                setCurrentPage(1);
              }}
              className="focus:outline-none bg-[var(--c2)]/50 text-[var(--c5)] p-2 rounded-xl cursor-pointer border border-[var(--c3)] hover:border-[var(--c4)]"
            />
          </div>
        </div>

        <ClockRequestList
          requests={paginatedClockRequests}
          onEdit={handleReview}
        />
      </Card>

      {selectedRequest && (
        <ClockRequestReviewModal
          isOpen={isModalOpen}
          onClose={handleModalClose}
          clockRequest={selectedRequest}
          onSuccess={handleSuccess}
        />
      )}
    </div>
  );
}
